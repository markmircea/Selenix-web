# Workflows

Workflows are the heart of Selenix. They represent a series of actions that automate tasks in your browser. Think of a workflow as a recipe - it contains step-by-step instructions that Selenix follows to complete a task.

## What is a Workflow?

A workflow is a collection of **actions** that are executed in sequence. Each action represents something you would normally do manually in a browser:

- Clicking buttons
- Filling out forms  
- Navigating between pages
- Extracting data
- Making decisions based on page content

## Workflow Components

### Actions
Individual steps in your workflow. Examples:
- `Click` - Click on a button or link
- `Type` - Enter text into a field
- `Navigate` - Go to a specific URL
- `Extract` - Capture data from the page
- `Wait` - Pause execution for a specified time

### Selectors
Instructions that tell Selenix which element to interact with on a page. Selenix automatically generates smart selectors that work even when pages change slightly.

### Variables
Storage containers for data that can be used throughout your workflow. Perfect for storing extracted data or user inputs.

### Conditions
Logic that allows your workflow to make decisions and branch into different paths based on what it finds on the page.

## Creating Workflows

### Method 1: Recording (Recommended for Beginners)

1. Click **New Workflow**
2. Click **Start Recording**
3. Perform actions in your browser
4. Click **Stop Recording**
5. Review and edit the generated workflow

### Method 2: Manual Building (Advanced Users)

1. Click **New Workflow**
2. Click **Build Manually**
3. Drag actions from the sidebar
4. Configure each action's properties
5. Connect actions in the desired sequence

## Workflow Types

### Linear Workflows
Execute actions one after another in a straight line. Perfect for simple, predictable tasks.

```
Navigate → Click → Type → Submit → Extract
```

### Conditional Workflows  
Include decision points that change behavior based on page content.

```
Navigate → Check if element exists
    ├─ Yes: Click element → Extract data
    └─ No: Try alternative approach
```

### Loop Workflows
Repeat a set of actions multiple times or until a condition is met.

```
Navigate → Loop: Find next item
    ├─ Extract item data
    ├─ Click "Next" button  
    └─ Continue until no more items
```

## Best Practices

### Naming Conventions
- Use descriptive names: `"Extract Product Prices from Amazon"`
- Include the target website: `"LinkedIn Profile Scraper"`
- Add version numbers for iterations: `"Email Signup v2"`

### Organization
- Group related workflows in folders
- Use tags to categorize workflows by purpose
- Add detailed descriptions for complex workflows

### Error Handling
- Add wait times for slow-loading pages
- Use conditional logic for elements that might not exist
- Include fallback actions for when primary methods fail

### Maintenance
- Test workflows regularly to catch website changes
- Update selectors when pages are redesigned  
- Keep workflows simple and focused on single tasks

## Workflow Settings

### Execution Speed
- **Fast**: Minimal delays between actions
- **Normal**: Standard timing that works for most sites
- **Slow**: Extra delays for slow websites or complex pages

### Browser Options
- **Headless**: Run without showing browser window
- **Incognito**: Use private browsing mode
- **Custom User Agent**: Appear as different browser/device

### Error Handling
- **Stop on Error**: Halt execution when something goes wrong
- **Continue on Error**: Skip failed actions and continue
- **Retry Failed Actions**: Attempt failed actions multiple times

Ready to learn about the individual building blocks? Check out our [Actions](#actions) guide next!
