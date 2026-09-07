OBEDA DORMITORIES — CSS SPLIT

What changed:
- assets/css/style.css was divided into logical component files.
- index.php now loads the split CSS files in the correct cascade order.
- login.css and signup.css were left unchanged.
- No HTML layout, PHP logic, JavaScript, or visual values were intentionally changed.

Files:
assets/css/base.css
assets/css/header.css
assets/css/home.css
assets/css/about.css
assets/css/rooms.css
assets/css/gallery.css
assets/css/contact.css
assets/css/responsive.css
index.php

Replace the corresponding files in your project. The original style.css is no longer loaded by index.php and can be removed after confirming the site works.
