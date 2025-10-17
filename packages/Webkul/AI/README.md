# Paragraph Improver Package

A Laravel package for improving paragraphs using OpenRouteAI API, integrated into Krayin CRM with reusable textarea components.

## Features

- ✅ **Improve any paragraph** using OpenRouteAI's powerful language models
- ✅ **Reusable Blade component** - Add AI to any textarea with one attribute
- ✅ **Simple web interface** for testing
- ✅ **REST API endpoints** for integration
- ✅ **Built-in connection testing**
- ✅ **Support for multiple AI models**
- ✅ **Character limit validation** (10-10,000 characters)
- ✅ **Dark mode support**
- ✅ **Visual before/after comparison**

## Installation

This package is already integrated into the Krayin CRM. The autoloader and service provider are registered.

## Configuration

The package uses the existing Magic AI configuration from Krayin CRM:
- API Key: `general.magic_ai.settings.api_key`
- Model: `general.magic_ai.settings.model` or `general.magic_ai.settings.other_model`

## Quick Start

### 1. Using the AI-Enabled Textarea Component (Recommended)

Simply add `ai-enabled="true"` to any textarea in your forms:

```blade
<x-admin::form.control-group>
    <x-admin::form.control-group.label>
        Description
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="textarea"
        name="description"
        ai-enabled="true"
        rows="5"
        placeholder="Enter description..."
    />
</x-admin::form.control-group>
```

**That's it!** Users will see an "AI Improve" button in the textarea that they can click to enhance their text.

### 2. Using the Standalone Interface

Visit `/paragraph-improver` to access the testing interface with:
- Text input area
- Sample paragraphs for quick testing
- API connection status
- Before/after comparison
- Character count validation

### 3. Using the Component Demo

Visit `/paragraph-improver/demo-component` to see live examples of the AI-enabled textarea in action.

## Usage Examples

### Lead Notes with AI Improvement
```blade
<x-admin::form.control-group>
    <x-admin::form.control-group.label>
        @lang('admin::app.leads.notes')
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="textarea"
        name="notes"
        ai-enabled="true"
        rows="6"
        :value="$lead->notes ?? ''"
    />
</x-admin::form.control-group>
```

### Product Description with Validation
```blade
<x-admin::form.control-group>
    <x-admin::form.control-group.label class="required">
        @lang('admin::app.products.description')
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="textarea"
        name="description"
        ai-enabled="true"
        rules="required"
        rows="8"
        :value="$product->description ?? ''"
    />

    <x-admin::form.control-group.error control-name="description" />
</x-admin::form.control-group>
```

### Email Content
```blade
<x-admin::form.control-group>
    <x-admin::form.control-group.label>
        Email Content
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="textarea"
        name="content"
        ai-enabled="true"
        rows="10"
        placeholder="Compose your email..."
    />
</x-admin::form.control-group>
```

## API Endpoints

- `GET /paragraph-improver` - Main interface
- `GET /paragraph-improver/demo-component` - Component demo
- `POST /paragraph-improver/improve` - Improve a paragraph
- `GET /paragraph-improver/test-connection` - Test API connection
- `GET /paragraph-improver/models` - Get available models
- `GET /paragraph-improver/demo` - Get demo data

### API Example

```bash
curl -X POST http://your-domain.com/paragraph-improver/improve \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{"paragraph": "Your paragraph text here..."}'
```

### Response Format

```json
{
    "success": true,
    "original": "Original paragraph text...",
    "improved": "Improved paragraph text...",
    "model_used": "openai/gpt-4o-mini",
    "tokens_used": 150
}
```

## Component Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `ai-enabled` | boolean | `false` | Enable AI improvement button |
| `name` | string | required | Field name |
| `rows` | integer | - | Number of rows |
| `placeholder` | string | - | Placeholder text |
| `value` | string | - | Initial value |
| `rules` | string | - | Validation rules |

## How It Works

1. **User Input**: User types text in the textarea
2. **AI Button**: Click the "AI Improve" button in the top-right corner
3. **Validation**: System validates text length (10-10,000 characters)
4. **Processing**: Text is sent to OpenRouteAI for improvement
5. **Comparison Modal**: Shows original vs improved text side-by-side
6. **User Choice**: User can accept or reject the improvement
7. **Apply**: If accepted, improved text replaces original

## Requirements

- Laravel 9.0+
- Valid OpenRouteAI API key
- Internet connection
- Krayin CRM Magic AI configuration

## Documentation

- **[Textarea Component Guide](TEXTAREA_COMPONENT.md)** - Complete guide for using the AI-enabled textarea component
- **Service Class** - `ParagraphImproverService` for programmatic access
- **Controller** - `ParagraphImproverController` for routing and views

## File Structure

```
packages/Webkul/ParagraphImprover/
├── composer.json
├── README.md
├── TEXTAREA_COMPONENT.md
└── src/
    ├── Http/Controllers/
    │   └── ParagraphImproverController.php
    ├── Providers/
    │   └── ParagraphImproverServiceProvider.php
    ├── Resources/views/
    │   ├── index.blade.php (Main interface)
    │   └── demo.blade.php (Component demo)
    ├── Routes/
    │   └── web.php
    └── Services/
        └── ParagraphImproverService.php
```

## Testing

1. **Ensure your OpenRouteAI API key is configured** in Krayin CRM Magic AI settings
2. **Visit the main interface**: `/paragraph-improver`
3. **Try the component demo**: `/paragraph-improver/demo-component`
4. **Test in your forms**: Add `ai-enabled="true"` to any textarea

## Sample Paragraphs

The package includes sample paragraphs for quick testing:

1. **Basic Text**: A paragraph with grammar issues for improvement
2. **Lorem Ipsum**: Standard placeholder text
3. **Quick Brown Fox**: Typography testing text

## Error Handling

The package includes comprehensive error handling:
- ✅ API connection failures
- ✅ Invalid paragraphs (too short/long)
- ✅ Missing configuration
- ✅ AI service errors
- ✅ User-friendly error messages

## Best Practices

1. **Use for Content Fields**: Best for descriptions, notes, summaries, emails
2. **Not for Code**: Don't use for code snippets or structured data
3. **User Awareness**: Users should know AI is improving their text
4. **Review Changes**: Always review AI-improved text before saving
5. **API Limits**: Be aware of API rate limits and costs
6. **Sensitive Data**: Don't use for confidential information (text is sent to external API)

## Security Considerations

- Text is sent to external API (OpenRouteAI)
- Don't use for sensitive/confidential information
- Implement proper access controls
- Consider adding permission checks
- Validate all user input

## Performance

- API calls are made on-demand (not automatic)
- Text is validated before sending
- Loading states prevent multiple requests
- Results are cached per field
- Minimal performance impact on forms

## Customization

### Change Button Position
Edit `textarea-ai.blade.php` to adjust button placement

### Change Button Style
Customize the button classes in the component

### Disable for Specific Fields
Set `ai-enabled="false"` or omit the attribute

### Custom Validation
Add your own validation rules alongside AI functionality

## Troubleshooting

### AI Button Not Appearing
- Check if `ai-enabled="true"` is set
- Verify component is properly registered
- Clear cache: `php artisan view:clear`

### AI Not Working
- Check OpenRouteAI API key in Magic AI settings
- Verify ParagraphImprover routes are registered: `php artisan route:list --name=paragraph-improver`
- Check browser console for errors
- Test connection at `/paragraph-improver`

### Modal Not Showing
- Ensure Krayin's confirm modal component is available
- Check for JavaScript errors in console
- Verify Vue.js is loaded

### Text Not Updating
- Check browser console for errors
- Verify CSRF token is present
- Ensure form uses Vue.js v-model or proper binding

## Support

For issues and questions:
1. Check the documentation files
2. Test the connection at `/paragraph-improver`
3. Review browser console for errors
4. Verify API configuration in Magic AI settings

## License

MIT License

## Credits

- Built for Krayin CRM
- Powered by OpenRouteAI
- Developed by Webkul