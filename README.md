# Selenix Web

This is the website for Selenix.io, a browser automation tool. The website is built with HTML, CSS, and JavaScript with a component-based structure.

## Project Structure

```
Selenix-web/
├── components/
│   ├── components.css       # Main component styles importer
│   ├── components.js        # Main component loader
│   ├── embedded-navbar.html # Backup navbar for direct embedding
│   ├── embedded-footer.html # Backup footer for direct embedding
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
│   └── templates/           # Templates page and related files
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
```

Make sure to adjust the path to match your file structure (e.g., `../components/components.css` for pages in subdirectories).

### Troubleshooting Component Loading

If components aren't loading properly, here are some steps to debug and fix the issues:

1. **Check the browser console** for any errors related to component loading.

2. **Verify paths are correct** - The component system tries to detect the correct relative path based on your page location, but this might not always work correctly with all server setups.

3. **Use the embedded components** - If the dynamically loaded components aren't working, you can use the embedded versions as a fallback:
   - Copy the contents of `components/embedded-navbar.html` and `components/embedded-footer.html` directly into your page.
   - These embedded versions contain all the necessary HTML, CSS, and JavaScript inline.

4. **Check CORS issues** - When testing locally, some browsers might block the AJAX requests used to load components. Try using a local server instead of opening files directly.

5. **Add debug information** - You can add `console.log()` statements in the components.js file to help identify where things are breaking.

### Common Issues

- **Paths are incorrect**: The most common issue is incorrect paths when loading components from different directories.
- **Server limitations**: Some server setups might restrict AJAX requests to load HTML files.
- **JavaScript errors**: Ensure there are no JavaScript errors that might prevent component loading scripts from executing.

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

## Development

### Adding New Pages

When adding new pages:

1. Create the HTML file in the appropriate directory
2. Include the necessary component containers and scripts
3. Link to the global styles and any page-specific styles
4. Test navigation links to ensure they work correctly

### Modifying Components

When modifying components:

1. Update the component files in the components directory
2. Test the changes on various pages to ensure they work correctly
3. If necessary, update the embedded component versions as well

### Using Embedded Components as Fallback

If you're having issues with the dynamic component loading, you can use the embedded versions:

1. Open the embedded component files (`embedded-navbar.html` and `embedded-footer.html`)
2. Copy the contents directly into your HTML file where the component containers would be
3. Remove the component container divs and component loading scripts

## Running the Project

For local development:

1. Use a local server to avoid CORS issues with component loading
2. Open the site through the server (e.g., http://localhost:8000) rather than directly from files
3. Check the browser console for any errors during development
