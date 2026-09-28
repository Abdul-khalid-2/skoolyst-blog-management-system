<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\User;

/**
 * Decides who gets emailed for each app event and writes the (plain-text) message.
 * Delivery itself is EmailService's job. Every public method here is best-effort:
 * a notification failure is logged and swallowed, never surfaced to the user.
 *
 * Deliberately not emailed: a confirmation to a public commenter on submit. Anyone
 * can type any address into the comment form, so that would let strangers make this
 * blog send mail to arbitrary inboxes. The commenter only hears from us once a staff
 * member has approved the comment.
 */
class NotificationService {
    private const STAFF_REVIEWERS = ['admin', 'editor'];

    public function __construct(private EmailService $mail = new EmailService()) {}

    // --- Accounts ---

    public function userRegistered(array $user): void {
        $this->safely(function () use ($user) {
            $isAuthor = $user['role'] === 'author';
            $this->send($user['email'], 'Welcome to ' . $this->siteName(), 'user.welcome', [
                "Hi {$user['name']},",
                'Thanks for creating an account on ' . $this->siteName() . '.',
                $isAuthor
                    ? "You signed up as an author, so you can start writing straight away:\n" . url('/dashboard/posts/create')
                    : "Start reading the latest articles here:\n" . url('/blog'),
                "You can sign in any time at:\n" . url('/login'),
                "If you didn't create this account, you can ignore this email or reply to let us know.",
            ]);

            $this->sendToMany($this->adminRecipients(), "New {$user['role']} signup: {$user['name']}", 'admin.user_registered', [
                'A new account was just created on ' . $this->siteName() . '.',
                "Name:  {$user['name']}\nEmail: {$user['email']}\nRole:  {$user['role']}",
                "Manage users:\n" . url('/dashboard/users'),
            ], (int) $user['id']);
        });
    }

    /** Admin changed someone's role and/or active flag. */
    public function accountUpdated(array $before, array $after, array $actor): void {
        $this->safely(function () use ($before, $after, $actor) {
            $changes = [];
            if ($before['role'] !== $after['role']) {
                $changes[] = "Your role changed from {$before['role']} to {$after['role']}.";
            }
            if ((int) $before['active'] !== (int) $after['active']) {
                $changes[] = (int) $after['active'] === 1
                    ? 'Your account has been re-activated. You can sign in again.'
                    : 'Your account has been deactivated. Contact an administrator if you think this is a mistake.';
            }
            if (!$changes) return;

            $this->send($after['email'], 'Your ' . $this->siteName() . ' account was updated', 'user.account_updated', [
                "Hi {$after['name']},",
                "An administrator ({$actor['name']}) made changes to your account:",
                implode("\n", array_map(fn ($c) => "- {$c}", $changes)),
                "Sign in:\n" . url('/login'),
            ]);
        });
    }

    public function passwordChanged(array $user): void {
        $this->safely(function () use ($user) {
            $this->send($user['email'], 'Your password was changed', 'user.password_changed', [
                "Hi {$user['name']},",
                'The password for your ' . $this->siteName() . ' account was just changed (' . date('Y-m-d H:i') . ').',
                "If this was you, there's nothing else to do.",
                "If it wasn't, contact an administrator immediately so the account can be secured.",
            ]);
        });
    }

    // --- Posts ---

    public function postCreated(array $post, array $actor): void {
        $this->safely(function () use ($post, $actor) {
            $published = $post['status'] === 'published';
            $owner = $this->owner($post) ?? $actor;

            $this->send($owner['email'], ($published ? 'Your post is live: ' : 'Draft saved: ') . $post['title'], 'post.created', [
                "Hi {$owner['name']},",
                $published
                    ? "Your post \"{$post['title']}\" has been published and is now live:\n" . $this->postUrl($post)
                    : "Your post \"{$post['title']}\" was saved as a draft. It isn't public yet — publish it when you're ready.",
                "Edit it any time:\n" . $this->editUrl($post),
            ]);

            // Editors/admins keep an eye on what authors put out; they don't need mail about each other's posts.
            if ($actor['role'] === 'author') {
                $this->sendToMany($this->staffReviewers(), "New post by {$actor['name']}: {$post['title']}", 'staff.post_created', [
                    "{$actor['name']} just created a post.",
                    "Title:  {$post['title']}\nStatus: {$post['status']}",
                    $published ? "View it:\n" . $this->postUrl($post) : null,
                    "Review / edit:\n" . $this->editUrl($post),
                ], (int) $actor['id']);
            }
        });
    }

    public function postUpdated(array $before, array $after, array $actor): void {
        $this->safely(function () use ($before, $after, $actor) {
            $owner = $this->owner($after);
            $justPublished = $before['status'] !== 'published' && $after['status'] === 'published';
            $unpublished = $before['status'] === 'published' && $after['status'] !== 'published';
            $editedByOther = $owner && (int) $owner['id'] !== (int) $actor['id'];

            $statusLine = match (true) {
                $justPublished => "It has been published and is now live:\n" . $this->postUrl($after),
                $unpublished => 'It has been moved back to draft and is no longer public.',
                $after['status'] === 'published' => "View it:\n" . $this->postUrl($after),
                default => 'It is still a draft.',
            };

            if ($owner) {
                $subject = $justPublished ? "Your post is live: {$after['title']}"
                    : ($editedByOther ? "{$actor['name']} edited your post: {$after['title']}" : "Post updated: {$after['title']}");
                $this->send($owner['email'], $subject, 'post.updated', [
                    "Hi {$owner['name']},",
                    $editedByOther
                        ? "{$actor['name']} ({$actor['role']}) made changes to your post \"{$after['title']}\"."
                        : "Your changes to \"{$after['title']}\" were saved.",
                    $before['title'] !== $after['title'] ? "Title changed from \"{$before['title']}\" to \"{$after['title']}\"." : null,
                    $statusLine,
                    "Edit it:\n" . $this->editUrl($after),
                ]);
            }

            if ($actor['role'] === 'author') {
                $this->sendToMany($this->staffReviewers(), "Post updated by {$actor['name']}: {$after['title']}", 'staff.post_updated', [
                    "{$actor['name']} updated a post.",
                    "Title:  {$after['title']}\nStatus: {$before['status']} → {$after['status']}",
                    "Review / edit:\n" . $this->editUrl($after),
                ], (int) $actor['id']);
            }
        });
    }

    public function postDeleted(array $post, array $actor): void {
        $this->safely(function () use ($post, $actor) {
            $owner = $this->owner($post);
            if (!$owner || (int) $owner['id'] === (int) $actor['id']) return;

            $this->send($owner['email'], "Your post was deleted: {$post['title']}", 'post.deleted', [
                "Hi {$owner['name']},",
                "{$actor['name']} ({$actor['role']}) deleted your post \"{$post['title']}\".",
                'If you have questions about this, please reach out to them or an administrator.',
            ]);
        });
    }

    // --- Comments ---

    /** Tells the post's author (or, for an authorless post, the admins/editors) that a comment awaits moderation. */
    public function commentSubmitted(array $post, array $comment): void {
        $this->safely(function () use ($post, $comment) {
            $owner = $this->owner($post);
            $recipients = $owner && (int) $owner['active'] === 1 ? [$owner] : $this->staffReviewers();

            $this->sendToMany($recipients, "New comment awaiting approval on \"{$post['title']}\"", 'comment.submitted', [
                "A reader left a comment on \"{$post['title']}\". It won't appear on the site until it's approved.",
                "From: {$comment['author_name']} <{$comment['author_email']}>",
                "Comment:\n" . $this->quote($comment['body']),
                "Approve or reject it here:\n" . url('/dashboard/comments'),
            ]);
        });
    }

    public function commentApproved(array $comment, array $post): void {
        $this->safely(function () use ($comment, $post) {
            $this->send($comment['author_email'], "Your comment on \"{$post['title']}\" is live", 'comment.approved', [
                "Hi {$comment['author_name']},",
                "Thanks for joining the conversation — your comment on \"{$post['title']}\" has been approved and is now visible:",
                $this->postUrl($post) . '#comments',
                "Your comment:\n" . $this->quote($comment['body']),
            ]);
        });
    }

    // --- Internals ---

    private function send(string $to, string $subject, string $event, array $paragraphs): void {
        $body = implode("\n\n", array_filter($paragraphs, fn ($p) => $p !== null && $p !== ''));
        $body .= "\n\n— The " . $this->siteName() . " team\n" . url('/');
        $this->mail->queue($to, $subject, $body, $event);
    }

    /** One email per unique address, skipping $excludeUserId (normally the person who triggered the event). */
    private function sendToMany(array $recipients, string $subject, string $event, array $paragraphs, ?int $excludeUserId = null): void {
        $seen = [];
        foreach ($recipients as $r) {
            if ($excludeUserId !== null && isset($r['id']) && (int) $r['id'] === $excludeUserId) continue;
            $email = strtolower(trim((string) ($r['email'] ?? '')));
            if ($email === '' || isset($seen[$email])) continue;
            $seen[$email] = true;
            $name = trim((string) ($r['name'] ?? ''));
            $this->send($email, $subject, $event, array_merge([$name !== '' ? "Hi {$name}," : 'Hi,'], $paragraphs));
        }
    }

    private function owner(array $post): ?array {
        return !empty($post['author_id']) ? User::findById((int) $post['author_id']) : null;
    }

    private function staffReviewers(): array {
        return User::activeWithRoles(self::STAFF_REVIEWERS);
    }

    private function adminRecipients(): array {
        $recipients = User::activeWithRoles(['admin']);
        $extra = (require dirname(__DIR__, 2) . '/config/mail.php')['admin_address'];
        if ($extra !== '') $recipients[] = ['name' => '', 'email' => $extra];
        return $recipients;
    }

    private function postUrl(array $post): string {
        return url('/post/' . $post['slug']);
    }

    private function editUrl(array $post): string {
        return url('/dashboard/posts/' . $post['id'] . '/edit');
    }

    private function quote(string $text): string {
        $text = trim(mb_substr($text, 0, 2000));
        return implode("\n", array_map(fn ($line) => '> ' . $line, preg_split('/\R/', $text)));
    }

    private function siteName(): string {
        return 'Skoolyst Blog';
    }

    private function safely(callable $fn): void {
        try {
            $fn();
        } catch (\Throwable $e) {
            error_log('[' . date('Y-m-d H:i:s') . '] [email] Notification failed: ' . $e->getMessage() . "\n", 3, dirname(__DIR__, 2) . '/storage/logs/app.log');
        }
    }
}
