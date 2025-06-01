# File Preview System

## Overview

The file preview system allows users to view file contents in a sliding panel that animates in from the right side when clicking on files in the file browser.

## Features

### Supported File Types

#### Images
- **Extensions**: jpg, jpeg, png, gif, webp, svg, bmp, ico
- **Preview**: Full image display with proper scaling
- **Behavior**: Images are displayed directly in the preview panel

#### Text Files
- **Extensions**: txt, md, markdown, csv, json, xml, yaml, yml, html, htm, css, js, ts, jsx, tsx, vue, php, py, rb, java, c, cpp, h, cs, go, rs, swift, kt, scala, sh, bash, zsh, sql, log, conf, ini, env, gitignore, dockerfile
- **Preview**: Syntax-highlighted text content
- **Limitations**: Content is truncated at 1MB for performance

#### Other Files
- **Behavior**: Shows "Preview not available" message with download option

### User Interface

#### File List
- Click on any file row to open preview
- File table automatically resizes to 2/3 width when preview is open
- Smooth transitions and animations

#### Preview Panel
- **Header**: File icon, name, and close button
- **Content Area**: File preview based on type
- **Metadata Section**: File details (name, size, modified date, type)
- **Actions**: Download and Delete buttons

#### Animations
- Slide-in from right: 300ms ease-in-out transition
- Slide-out to right: 300ms ease-in-out transition
- File list width adjustment: smooth transition

## Technical Implementation

### Frontend (Alpine.js)
- State management for preview panel
- AJAX requests for content loading
- Conditional rendering based on file types
- Click event handling with event propagation control

### Backend (Laravel)
- New route: `GET /files/{connection}/preview/{path}`
- Enhanced FileOperationController with preview method
- MIME type detection and content handling
- Security: User authorization and file existence checks

### File Structure
```
resources/views/files/browse.blade.php  - Main file browser with preview UI
app/Http/Controllers/FileOperationController.php - Preview logic
routes/web.php - Preview route definition
```

## API Endpoints

### Preview Endpoint
```
GET /files/{connection}/preview/{path}
```

**Response for Images:**
- Returns raw image data with appropriate Content-Type
- Includes caching headers

**Response for Text Files (JSON):**
```json
{
    "content": "file content here...",
    "type": "text",
    "filename": "example.txt",
    "size": 1024
}
```

**Response for Unsupported Files:**
```json
{
    "error": "Preview not available for this file type",
    "type": "unsupported",
    "extension": "pdf",
    "mimeType": "application/pdf"
}
```

## Browser Compatibility

- Modern browsers with ES6+ support
- Alpine.js v3.x compatible
- CSS transitions and transforms support required

## Security Considerations

- User authorization required for all preview requests
- File path validation and sanitization
- CSRF protection on all AJAX requests
- Content size limits to prevent memory issues
- No execution of file contents (preview only)

## Future Enhancements

Potential improvements could include:
- PDF preview support
- Video/audio preview
- Syntax highlighting for code files
- Thumbnail generation for images
- Preview caching
- Full-screen preview mode
- Keyboard navigation (arrow keys) 