<?php /** Static about page. */ ?>
<section class="hero container">
  <h1>Making Education Easier to Understand, Explore, and Improve</h1>
  <p>Skoolyst is an education-focused platform that shares practical knowledge, useful resources, and thoughtful insights for students, teachers, parents, schools, and education professionals.</p>
</section>

<section class="container about-section">
  <h2>Who We Are</h2>
  <p>Skoolyst is built around a simple idea: good educational information should be easy to find, easy to understand, and useful in real life.</p>
  <p>Through our platform and blog, we explore topics related to education, learning, technology, AI in education, teaching strategies, student development, schools, and the future of learning.</p>
</section>

<section class="container about-section">
  <h2>What You'll Find on Skoolyst</h2>
  <div class="post-grid">
    <?php
    $topics = [
        ['icon' => '🎓', 'title' => 'Student Learning', 'text' => 'Study strategies, academic guidance, and learning resources.'],
        ['icon' => '👩‍🏫', 'title' => 'Teachers & Teaching', 'text' => 'Teaching ideas, classroom strategies, and professional insights.'],
        ['icon' => '🤖', 'title' => 'AI & Education', 'text' => 'Practical and responsible ways AI is changing learning.'],
        ['icon' => '🏫', 'title' => 'Schools & Education', 'text' => 'Ideas, trends, and technology shaping modern schools.'],
        ['icon' => '📚', 'title' => 'Educational Resources', 'text' => 'Useful guides, explanations, MCQs, and learning materials.'],
        ['icon' => '🌍', 'title' => 'Future of Education', 'text' => 'Research, trends, and ideas about where education is heading.'],
    ];
    foreach ($topics as $topic): ob_start(); ?>
      <p class="about-card-icon" aria-hidden="true"><?= $topic['icon'] ?></p>
      <h3><?= clean($topic['title']) ?></h3>
      <p><?= clean($topic['text']) ?></p>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endforeach; ?>
  </div>
</section>

<section class="container about-section">
  <h2>Our Mission</h2>
  <p>Our mission is to make quality educational knowledge more accessible and useful.</p>
  <p>We want to help students learn better, teachers teach more effectively, parents make informed decisions, and schools discover ideas that can improve education.</p>
</section>

<section class="container about-section">
  <h2>Our Approach</h2>
  <div class="stat-grid">
    <?php
    $principles = [
        ['title' => 'Useful', 'text' => 'We focus on information that readers can actually apply.'],
        ['title' => 'Simple', 'text' => 'Complex educational and technological topics should be understandable without unnecessary jargon.'],
        ['title' => 'Responsible', 'text' => 'Especially when discussing AI and education, we aim to promote thoughtful, ethical, and responsible use of technology.'],
    ];
    foreach ($principles as $principle): ob_start(); ?>
      <h3><?= clean($principle['title']) ?></h3>
      <p><?= clean($principle['text']) ?></p>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endforeach; ?>
  </div>
</section>

<section class="container about-section">
  <h2>Who Is Skoolyst For?</h2>
  <p>Skoolyst is for anyone who cares about better education — students, teachers, parents, schools, education professionals, and lifelong learners.</p>
</section>

<section class="container about-cta">
  <h2>Learn Something Useful. Discover Something New.</h2>
  <p>Explore the Skoolyst Blog for practical educational insights, resources, and ideas for the future of learning.</p>
  <?php component('button', ['label' => 'Explore the Blog', 'type' => 'link', 'href' => url('/blog'), 'variant' => 'secondary']); ?>
</section>
