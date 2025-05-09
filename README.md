# Selenix Web

This is the website for Selenix.io, a browser automation tool. The website is built with HTML, CSS, and JavaScript with a component-based structure.

## Project Structure

```
Selenix-web/
├── components/
│   ├── components.css       # Main component styles importer
│   ├── components.js        # Main component loader
│   ├── navbar/              # Navbar component
│   │   ├── navbar.html      # Navbar HTML structure
│   │   ├── navbar.css       # Navbar-specific styles
│   │   └── navbar.js        # Navbar functionality
│   └── footer/              # Footer component
│       ├── footer.html      # Footer HTML structure
│       ├── footer.css       # Footer-specific styles
│       └── footer.js        # Footer functionality
├── docs/                    # Documentation pages
├── product/                 # Product pages
├── index.html               # Main landing page
├── styles.css               # Global styles
└── script.js                # Global JavaScript
```

## Component System

The website uses a simple component system to reuse common elements like the navbar and footer across multiple pages. This approach keeps the codebase DRY (Don't Repeat Yourself) and makes updates easier.

### How to Use Components

To include components in a page:

1. Add the component container div(s) to your HTML:
```html
<!-- Navbar Component -->
<div id="navbar-container"></div>

<!-- Page content goes here -->

<!-- Footer Component -->
<div id="footer-container"></div>
```

2. Include the necessary component scripts and styles:
```html
<link rel="stylesheet" href="path/to/components/components.css">
<script src="path/to/components/components.js"></script>
<script src="path/to/components/navbar/navbar.js"></script>
<script src="path/to/components/footer/footer.js"></script>
```

## URL Structure

The navbar links to corresponding folders following this pattern:
- Product pages: `/product/[page].html`
- Documentation: `/docs/[page].html`

## Styling

The site uses CSS variables for consistent theming:
- `--primary-color`: Main brand color
- `--secondary-color`: Secondary brand color
- `--text-color`: Regular text color
- `--heading-color`: Heading text color
- `--light-bg`: Light background color
- `--border-color`: Border color
- `--card-shadow`: Shadow for card elements
- `--hover-shadow`: Shadow for hover states

## JavaScript Features

- Smooth scrolling
- Mobile menu toggle
- Reveal animations on scroll
- Counter animations
- Dropdown menu functionality

## Browser Compatibility

The site is designed to work on modern browsers including:
- Chrome/Edge (latest versions)
- Firefox (latest versions)
- Safari (latest versions)

## Responsive Design

The site is responsive with breakpoints at:
- 1024px (tablets and smaller desktops)
- 768px (mobile devices)
- 480px (small mobile devices)
