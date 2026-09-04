# Obeda Dormitories - PHP Mockup Replica

This project recreates the supplied 1920 x 1080, 16:9 mockup as a PHP page with CSS and JavaScript.

## Run locally

Use a PHP server from this folder:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`.

## Replacing the placeholders

All photo/logo areas are `<img>` elements with fixed CSS dimensions. Replace the corresponding files in `assets/images/` while keeping the same filenames, and the replacement image will be cropped to the same displayed box using `object-fit: cover` (or `contain` for logos/icons).

Main replacement files include:

- `hero-building-placeholder.svg`
- `city-placeholder.svg`
- `story-placeholder.svg`
- `brand-placeholder-dark.svg`
- `brand-placeholder-light.svg`
- `bedspace-placeholder.svg`
- `female-room-placeholder.svg`
- `kitchen-placeholder.svg`
- `bathroom-placeholder.svg`
- `gallery-1-placeholder.svg` through `gallery-5-placeholder.svg`
- certification placeholders and icon placeholders

For photo replacements, keeping the subject's intended crop in mind will give the closest match. The CSS box itself does not resize when the photo changes.
