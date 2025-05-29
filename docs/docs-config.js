// docs-config.js - Configuration file for documentation structure
const docsConfig = {
    // Documentation structure - add new pages here
    sections: [
        {
            title: "Getting Started",
            icon: "fa-solid fa-rocket",
            pages: [
                {
                    id: "introduction",
                    title: "Introduction",
                    icon: "fa-solid fa-book-open",
                    file: "introduction.html"
                },
                {
                    id: "installation",
                    title: "Installation", 
                    icon: "fa-solid fa-download",
                    file: "installation.html"
                },
                {
                    id: "quick-start",
                    title: "Quick Start",
                    icon: "fa-solid fa-bolt",
                    file: "quick-start.html"
                }
            ]
        },
        {
            title: "Core Concepts",
            icon: "fa-solid fa-cubes",
            pages: [
                {
                    id: "workflows",
                    title: "Workflows",
                    icon: "fa-solid fa-diagram-project",
                    file: "workflows.html"
                },
                {
                    id: "actions",
                    title: "Actions",
                    icon: "fa-solid fa-play",
                    file: "actions.html"
                },
                {
                    id: "selectors",
                    title: "Selectors",
                    icon: "fa-solid fa-crosshairs",
                    file: "selectors.html"
                },
                {
                    id: "variables",
                    title: "Variables",
                    icon: "fa-solid fa-code",
                    file: "variables.html"
                }
            ]
        },
        {
            title: "Advanced Usage",
            icon: "fa-solid fa-graduation-cap",
            pages: [
                {
                    id: "conditional-logic",
                    title: "Conditional Logic",
                    icon: "fa-solid fa-code-branch",
                    file: "conditional-logic.html"
                },
                {
                    id: "loops",
                    title: "Loops",
                    icon: "fa-solid fa-repeat",
                    file: "loops.html"
                },
                {
                    id: "data-export",
                    title: "Data Export",
                    icon: "fa-solid fa-file-export",
                    file: "data-export.html"
                },
                {
                    id: "integrations",
                    title: "Integrations",
                    icon: "fa-solid fa-plug",
                    file: "integrations.html"
                }
            ]
        },
        {
            title: "Templates",
            icon: "fa-solid fa-puzzle-piece",
            pages: [
                {
                    id: "using-templates",
                    title: "Using Templates",
                    icon: "fa-solid fa-puzzle-piece",
                    file: "using-templates.html"
                },
                {
                    id: "creating-templates",
                    title: "Creating Templates",
                    icon: "fa-solid fa-plus",
                    file: "creating-templates.html"
                },
                {
                    id: "template-library",
                    title: "Template Library",
                    icon: "fa-solid fa-book",
                    file: "template-library.html"
                }
            ]
        }
    ],
    
    // Default page to load
    defaultPage: "introduction",
    
    // Base path for HTML content files
    contentPath: "./content/",
    
    // Site information
    site: {
        title: "Selenix Documentation",
        description: "Complete guide to using Selenix browser automation tool",
        logo: "selenix.io"
    }
};
