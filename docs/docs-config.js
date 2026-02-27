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
                    title: "AI-Powered Workflows",
                    icon: "fa-solid fa-diagram-project",
                    file: "workflows.html"
                },
                {
                    id: "ai-assistant",
                    title: "AI Assistant",
                    icon: "fa-solid fa-robot",
                    file: "ai-assistant.html"
                },
                {
                    id: "actions",
                    title: "Commands & Actions",
                    icon: "fa-solid fa-play",
                    file: "actions.html"
                },
                {
                    id: "selectors",
                    title: "Smart Selectors",
                    icon: "fa-solid fa-crosshairs",
                    file: "selectors.html"
                },
                {
                    id: "variables",
                    title: "Variables & Data",
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
                    icon: "fa-solid fa-brain",
                    file: "conditional-logic.html"
                },
                {
                    id: "loops",
                    title: "Loops",
                    icon: "fa-solid fa-repeat",
                    file: "loops.html"
                },
                {
                    id: "scheduling",
                    title: "Test Scheduling",
                    icon: "fa-solid fa-calendar-alt",
                    file: "scheduling.html"
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
                },
                {
                    id: "mcp-server",
                    title: "MCP Server (Claude Desktop)",
                    icon: "fa-solid fa-plug-circle-bolt",
                    file: "mcp-server.html"
                }
            ]
        },

        {
            title: "Templates & Sharing",
            icon: "fa-solid fa-puzzle-piece",
            pages: [
                {
                    id: "templates",
                    title: "Templates",
                    icon: "fa-solid fa-puzzle-piece",
                    file: "templates.html"
                },
                {
                    id: "template-library",
                    title: "Template Library",
                    icon: "fa-solid fa-book",
                    file: "template-library.html"
                },
                {
                    id: "sharing",
                    title: "Sharing & Collaboration",
                    icon: "fa-solid fa-users",
                    file: "sharing.html"
                }
            ]
        },
        {
            title: "Reference",
            icon: "fa-solid fa-book-bookmark",
            pages: [
                {
                    id: "command-reference",
                    title: "Command Reference",
                    icon: "fa-solid fa-terminal",
                    file: "command-reference.html"
                },
                {
                    id: "troubleshooting",
                    title: "Troubleshooting",
                    icon: "fa-solid fa-wrench",
                    file: "troubleshooting.html"
                },
                {
                    id: "faq",
                    title: "FAQ",
                    icon: "fa-solid fa-circle-question",
                    file: "faq.html"
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
        title: "Selenix AI Documentation",
        description: "Complete guide to using Selenix AI-powered browser automation and web scraping platform",
        logo: "selenix.io"
    }
};