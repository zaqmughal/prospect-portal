# Project Rules

This directory contains the modern Cursor Project Rules that have replaced the legacy `.cursorrules` file. Each rule is focused on specific aspects of development and includes metadata for better organization and targeting.

## 📁 Rule Files Overview

### 🔒 **security-first-approach.mdc**
- **Scope**: All PHP files, views, routes, and tests
- **Type**: Always Applied
- **Purpose**: Security-first approach for all code suggestions and reviews
- **Key Areas**: Input validation, output encoding, authentication, authorization, CSRF protection

### ♿ **accessibility-standards.mdc**
- **Scope**: Views, SCSS, and JavaScript files
- **Type**: Auto Attached
- **Purpose**: WCAG 2.2 AA accessibility standards
- **Key Areas**: Semantic HTML, ARIA attributes, keyboard navigation, color contrast

### 📚 **php-laravel-standards.mdc**
- **Scope**: PHP files in app, tests, database, and config directories
- **Type**: Auto Attached
- **Purpose**: PHP and Laravel coding standards
- **Key Areas**: PSR-12 compliance, Laravel conventions, SOLID principles, type hints

### 🧪 **testing-requirements.mdc**
- **Scope**: Test files and PHP application files
- **Type**: Auto Attached
- **Purpose**: Comprehensive testing requirements
- **Key Areas**: Unit tests, feature tests, code coverage, test quality standards

### ⚡ **performance-optimization.mdc**
- **Scope**: PHP, JavaScript, and SCSS files
- **Type**: Auto Attached
- **Purpose**: Performance optimization guidelines
- **Key Areas**: Database optimization, caching, frontend performance, monitoring

### 🎨 **frontend-development.mdc**
- **Scope**: Views, SCSS, and JavaScript files
- **Type**: Auto Attached
- **Purpose**: Frontend development standards
- **Key Areas**: HTML structure, CSS architecture, JavaScript standards, responsive design

### 🗄️ **backend-development.mdc**
- **Scope**: PHP files in app, routes, and config directories
- **Type**: Auto Attached
- **Purpose**: Backend development standards
- **Key Areas**: Laravel best practices, API development, security implementation

### ⚠️ **safety-protocols.mdc**
- **Scope**: Migrations, commands, and deployment files
- **Type**: Always Applied
- **Purpose**: Safety protocols for dangerous operations
- **Key Areas**: Database safety, deployment safety, security protocols

### 📋 **definition-of-done.mdc**
- **Scope**: All file types
- **Type**: Always Applied
- **Purpose**: Definition of Done checklist
- **Key Areas**: Quality gates, testing requirements, deployment readiness

### 🚀 **general-development.mdc**
- **Scope**: All files
- **Type**: Always Applied
- **Purpose**: General development guidelines
- **Key Areas**: Documentation, code review, communication, workflow

## 📚 **Documentation & Context Rules**

### 📝 **documentation-standards.mdc**
- **Scope**: All documentation files, README files, and inline code documentation
- **Type**: Auto Attached
- **Purpose**: Comprehensive documentation standards and templates
- **Key Areas**: API documentation, feature specs, inline comments, documentation quality

### 🎯 **feature-specification-templates.mdc**
- **Scope**: Feature specifications, user stories, and technical requirements
- **Type**: Agent Requested
- **Purpose**: Standardized templates for feature documentation and specifications
- **Key Areas**: Business context, user experience, technical architecture, implementation planning

### 🏗️ **project-context.mdc**
- **Scope**: All project files and development work
- **Type**: Always Applied
- **Purpose**: Comprehensive project context and architectural understanding
- **Key Areas**: System architecture, business context, technical stack, development workflow

### 🔌 **api-documentation-standards.mdc**
- **Scope**: API endpoints, routes, and integration documentation
- **Type**: Auto Attached
- **Purpose**: Comprehensive API documentation standards and templates
- **Key Areas**: Endpoint documentation, authentication, data models, webhooks, versioning

## 🔧 Rule Types Explained

- **Always Applied**: Automatically included in AI context
- **Auto Attached**: Included when files matching glob patterns are referenced
- **Agent Requested**: AI decides whether to include (requires description)
- **Manual**: Only included when explicitly mentioned with `@ruleName`

## 📝 Usage

These rules will automatically be applied based on the file types you're working with. The AI will:

1. **Always** apply security, safety, definition of done, general development, and project context rules
2. **Automatically attach** relevant rules when you work with specific file types
3. **Provide focused guidance** based on the context of your current work
4. **Reference documentation templates** when working on documentation or feature specs

## 🔄 Migration from .cursorrules

The original `.cursorrules` file has been backed up as `.cursorrules.backup`. The new Project Rules system provides:

- **Better Organization**: Rules are separated by concern
- **Targeted Application**: Rules apply only when relevant
- **Easier Maintenance**: Each rule can be updated independently
- **Better Performance**: Only relevant rules are loaded
- **Version Control**: Rules are properly tracked in git
- **Enhanced Context**: Rich documentation and feature specification templates

## 🎯 Benefits

- **Focused Guidance**: Get specific advice based on what you're working on
- **Better Performance**: Only relevant rules are loaded into context
- **Easier Maintenance**: Update individual rules without affecting others
- **Team Collaboration**: Rules are version-controlled and shared
- **Flexible Application**: Mix and match rules as needed
- **Rich Documentation**: Comprehensive templates for documentation and feature specs
- **Project Context**: Deep understanding of the platform architecture and business context