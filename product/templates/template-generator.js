// template-generator.js
// Generates workflow structures for different template categories

export class TemplateGenerator {
    generateTemplate(template) {
        return {
            metadata: {
                id: template.id,
                title: template.title,
                description: template.description,
                category: template.category,
                tags: template.tags || [],
                version: "1.0",
                created_at: template.created_at,
                created_by: "Selenix Team"
            },
            workflow: this.generateWorkflowStructure(template),
            settings: this.generateTemplateSettings(template)
        };
    }
    
    generateWorkflowStructure(template) {
        const baseWorkflow = {
            steps: [],
            variables: {},
            conditions: []
        };
        
        switch (template.category) {
            case 'data-scraping':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{target_url}}",
                            description: "Navigate to target website"
                        },
                        {
                            id: 2,
                            type: "wait",
                            action: "wait_for_element",
                            selector: "{{main_content_selector}}",
                            timeout: 10000,
                            description: "Wait for page to load"
                        },
                        {
                            id: 3,
                            type: "extract",
                            action: "scrape_data",
                            selectors: {
                                title: "{{title_selector}}",
                                description: "{{description_selector}}",
                                price: "{{price_selector}}"
                            },
                            description: "Extract data from page"
                        },
                        {
                            id: 4,
                            type: "output",
                            action: "save_data",
                            format: "csv",
                            filename: "scraped_data.csv",
                            description: "Save extracted data"
                        }
                    ],
                    variables: {
                        target_url: "https://example.com",
                        main_content_selector: "main",
                        title_selector: "h1",
                        description_selector: ".description",
                        price_selector: ".price"
                    }
                };
                
            case 'form-filling':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{form_url}}",
                            description: "Navigate to form page"
                        },
                        {
                            id: 2,
                            type: "input",
                            action: "fill_field",
                            selector: "{{name_field_selector}}",
                            value: "{{user_name}}",
                            description: "Fill name field"
                        },
                        {
                            id: 3,
                            type: "input",
                            action: "fill_field",
                            selector: "{{email_field_selector}}",
                            value: "{{user_email}}",
                            description: "Fill email field"
                        },
                        {
                            id: 4,
                            type: "action",
                            action: "click",
                            selector: "{{submit_button_selector}}",
                            description: "Submit the form"
                        }
                    ],
                    variables: {
                        form_url: "https://example.com/form",
                        name_field_selector: "input[name='name']",
                        email_field_selector: "input[name='email']",
                        submit_button_selector: "button[type='submit']",
                        user_name: "Your Name",
                        user_email: "your.email@example.com"
                    }
                };
                
            case 'social-media':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{social_platform_url}}",
                            description: "Navigate to social media platform"
                        },
                        {
                            id: 2,
                            type: "auth",
                            action: "login",
                            username_selector: "{{username_selector}}",
                            password_selector: "{{password_selector}}",
                            login_button_selector: "{{login_button_selector}}",
                            description: "Login to platform"
                        },
                        {
                            id: 3,
                            type: "action",
                            action: "create_post",
                            content_selector: "{{post_content_selector}}",
                            content: "{{post_content}}",
                            description: "Create new post"
                        },
                        {
                            id: 4,
                            type: "action",
                            action: "click",
                            selector: "{{publish_button_selector}}",
                            description: "Publish the post"
                        }
                    ],
                    variables: {
                        social_platform_url: "https://platform.com",
                        username_selector: "input[name='username']",
                        password_selector: "input[name='password']",
                        login_button_selector: "button[type='submit']",
                        post_content_selector: "textarea[placeholder='What\\'s happening?']",
                        publish_button_selector: "button[data-testid='publish']",
                        post_content: "Your post content here"
                    }
                };
                
            case 'e-commerce':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{product_url}}",
                            description: "Navigate to product page"
                        },
                        {
                            id: 2,
                            type: "extract",
                            action: "get_price",
                            selector: "{{price_selector}}",
                            description: "Extract current price"
                        },
                        {
                            id: 3,
                            type: "condition",
                            action: "compare_price",
                            current_price: "{{extracted_price}}",
                            target_price: "{{target_price}}",
                            operator: "less_than",
                            description: "Check if price dropped"
                        },
                        {
                            id: 4,
                            type: "notification",
                            action: "send_alert",
                            message: "Price dropped to {{extracted_price}}!",
                            condition: "price_drop_detected",
                            description: "Send price alert"
                        }
                    ],
                    variables: {
                        product_url: "https://store.com/product",
                        price_selector: ".price-current",
                        target_price: "100.00"
                    }
                };
                
            case 'marketing':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{competitor_url}}",
                            description: "Navigate to competitor website"
                        },
                        {
                            id: 2,
                            type: "extract",
                            action: "scrape_content",
                            selectors: {
                                headlines: "{{headline_selector}}",
                                pricing: "{{pricing_selector}}",
                                features: "{{features_selector}}"
                            },
                            description: "Extract competitor data"
                        },
                        {
                            id: 3,
                            type: "analysis",
                            action: "compare_data",
                            previous_data: "{{stored_data}}",
                            current_data: "{{extracted_data}}",
                            description: "Compare with previous data"
                        },
                        {
                            id: 4,
                            type: "output",
                            action: "generate_report",
                            format: "html",
                            template: "competitor_analysis_template",
                            description: "Generate analysis report"
                        }
                    ],
                    variables: {
                        competitor_url: "https://competitor.com",
                        headline_selector: "h1, h2",
                        pricing_selector: ".pricing",
                        features_selector: ".features li"
                    }
                };
                
            default:
                return baseWorkflow;
        }
    }
    
    generateTemplateSettings(template) {
        return {
            execution: {
                delay_between_steps: 1000,
                timeout_per_step: 30000,
                retry_failed_steps: true,
                max_retries: 3
            },
            browser: {
                headless: false,
                window_size: {
                    width: 1280,
                    height: 720
                },
                user_agent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            },
            output: {
                save_screenshots: true,
                log_level: "info",
                export_format: "json"
            },
            notifications: {
                on_completion: true,
                on_error: true,
                email_notifications: false
            }
        };
    }
}
