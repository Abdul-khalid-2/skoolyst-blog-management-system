<?php /** Static privacy policy page. */ ?>
<section class="hero container">
  <h1>Privacy Policy</h1>
  <p>This explains what information Skoolyst actually collects, why, and how it's handled.</p>
</section>

<section class="container legal-page">
  <div class="legal-section">
    <h2>1. Introduction</h2>
    <p>Skoolyst respects your privacy and is committed to protecting the personal information of everyone who uses this platform — readers, commenters, authors, teachers, and administrators alike. This policy explains what information we collect, why we collect it, and how it's used.</p>
  </div>

  <div class="legal-section">
    <h2>2. Information We Collect</h2>
    <p>Skoolyst collects only what's needed to operate the blog:</p>
    <ul>
      <li><strong>Account information</strong> — your name, email address, and password (stored securely hashed, never in plain text) when you sign up or are given a staff account.</li>
      <li><strong>Content you submit</strong> — articles, tags, and images if you're an author, or your name, email address, and comment text if you leave a comment (a comment doesn't require an account).</li>
      <li><strong>Contact form submissions</strong> — the name, email, and message you enter if you use the Contact page.</li>
      <li><strong>Basic technical information</strong> — standard details like IP address and browser type that any web server logs as part of handling requests.</li>
    </ul>
    <p>We do not collect payment information, government ID numbers, or any sensitive personal data, and Skoolyst has no reason to ask for any.</p>
  </div>

  <div class="legal-section">
    <h2>3. How We Use Information</h2>
    <p>Information is used only to run the platform:</p>
    <ul>
      <li>To create and manage accounts, and keep you signed in</li>
      <li>To publish and manage the articles and comments you submit</li>
      <li>To moderate comments and articles for compliance with our <a href="<?= url('/terms') ?>">Terms &amp; Conditions</a></li>
      <li>To respond if you contact us directly</li>
      <li>To maintain the security of the platform and prevent spam or abuse</li>
    </ul>
    <p>We do not sell, rent, or trade personal information to third parties, and we don't use it for advertising.</p>
  </div>

  <div class="legal-section">
    <h2>4. User-Submitted Content</h2>
    <p>Anything you publish or submit publicly is, by nature, public. This includes your published articles, your author name and byline, and any comments you post along with the name you entered when commenting. Please don't include information in a comment or article that you don't want visible to any visitor of the site.</p>
  </div>

  <div class="legal-section">
    <h2>5. Cookies &amp; Analytics</h2>
    <p>Skoolyst uses a single essential cookie to keep you signed in between page visits. It's required for login to work and isn't used for tracking, advertising, or analytics.</p>
    <p>We do not currently use Google Analytics, advertising cookies, or any third-party tracking or analytics service.</p>
  </div>

  <div class="legal-section">
    <h2>6. Data Security</h2>
    <p>Passwords are never stored in plain text — they're hashed before being saved. Uploaded images are re-processed on our server before publication, which also strips hidden metadata (such as location data) that cameras and phones often embed in photo files. Access to the admin dashboard is role-restricted: authors can only manage their own posts, and only administrators can manage other user accounts. No system is completely immune to risk, but we take reasonable, proportionate steps to protect the information we hold.</p>
  </div>

  <div class="legal-section">
    <h2>7. Third-Party Services</h2>
    <p>Skoolyst is self-hosted and does not integrate any third-party analytics, advertising, or email marketing service. The one external resource in use is the CKEditor rich-text editor, loaded from its official CDN — and only inside the staff dashboard when writing or editing a post, never on pages visitors see. If that ever changes, this section will be updated to name the service.</p>
  </div>

  <div class="legal-section">
    <h2>8. Data Retention &amp; Deletion</h2>
    <p>Account information is kept for as long as the account is active. If you'd like your account deleted, contact an administrator — deleting an account does not delete the posts or comments already published under it; posts simply become unassigned rather than removed, so that other readers' access to published content isn't disrupted. Comments are not linked to an account at all (only the name and email typed at the time), so they aren't affected by account deletion either way.</p>
  </div>

  <div class="legal-section">
    <h2>9. Children's Privacy</h2>
    <p>Skoolyst is an educational platform and some content is written with students in mind, but the site does not currently verify the age of visitors or accept accounts specifically marketed to young children. If you believe a child has provided us with personal information without appropriate parental or guardian consent, please contact us and we will remove it.</p>
  </div>

  <div class="legal-section">
    <h2>10. Changes to This Privacy Policy</h2>
    <p>We may update this Privacy Policy from time to time as the platform evolves. Any changes take effect as soon as the updated policy is published on this page.</p>
  </div>

  <div class="legal-section">
    <h2>11. Contact Us</h2>
    <p>If you have questions about this Privacy Policy or how your information is handled, get in touch through our <a href="<?= url('/contact') ?>">Contact page</a>.</p>
  </div>

  <p class="legal-updated">Last updated: <?= date('F Y') ?></p>
</section>
