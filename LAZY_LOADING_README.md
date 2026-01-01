# Lazy Loading Optimization for index.html

## Overview
This implementation adds progressive/lazy loading to heavy sections of the homepage to improve initial page load performance. Sections are loaded as the user scrolls to them, rather than all at once.

## What Was Implemented

### 1. Lazy Loader Module (`js/lazy-loader.js`)
- Uses Intersection Observer API to detect when sections approach viewport
- Starts loading sections 200px before they enter viewport
- Supports multiple loading strategies:
  - Standard lazy load (just show/hide content)
  - Script-based lazy load (load external JS before showing)
  - API-based lazy load (fetch content via API)

### 2. Optimized Sections
The following heavy sections are now lazy loaded:

#### Featured Products Section (`block-products-carousel`)
- **ID**: `featured-products-section`
- Loads when scrolled into view
- Contains 10+ product cards with images

#### Sale Section (`block-sale`)
- **ID**: `sale-section`  
- Loads when scrolled into view
- Contains multiple sale items with heavy images

#### Block Zones (`block-zones-container`)
- **ID**: `zones-section`
- Already dynamically loaded, now optimized with lazy loading
- Contains category cards and additional product carousels

### 3. Deferred JavaScript Libraries

#### Three.js & D3.js (3D Map)
- **Before**: Loaded immediately on page load
- **After**: Only loaded when map section comes into view (300px before)
- Saves ~500KB+ on initial load

#### Other Scripts
- `block-zones.js` - Added `defer` attribute
- `blog-loader.js` - Added `defer` attribute

### 4. Background Images
- Sale section background image now uses `data-bg-image` attribute
- Loads lazily when section comes into view
- Smooth fade-in transition

## Performance Benefits

### Initial Load Reduction
- **Before**: All sections + heavy JS libraries loaded immediately
- **After**: Only above-the-fold content loads initially

### Estimated Savings
- Three.js: ~150KB
- D3.js: ~350KB  
- Deferred scripts: ~50-100KB
- Reduced initial DOM parsing: Significant improvement

### User Experience
- Faster Time to Interactive (TTI)
- Faster First Contentful Paint (FCP)
- Reduced initial JavaScript execution time
- Smooth loading experience as user scrolls

## How It Works

### Section Structure
```html
<div class="block block-products-carousel" data-lazy-load id="featured-products-section">
    <div class="lazy-section__content container">
        <!-- Section content -->
    </div>
</div>
```

### Loading Process
1. Page loads - sections marked with `data-lazy-load` are detected
2. Placeholder shown (loading spinner)
3. Intersection Observer monitors scroll position
4. When section approaches viewport (200px before):
   - Placeholder hidden
   - Content shown
   - Images lazy loaded
   - Background images loaded
5. Section marked as loaded (prevents reloading)

## CSS Classes

- `.lazy-section--loading` - Section is loading
- `.lazy-section--loaded` - Section has loaded
- `.lazy-section--error` - Error loading section
- `.lazy-section__placeholder` - Loading placeholder element
- `.lazy-section__content` - Actual content container

## Events

The lazy loader dispatches custom events:

- `lazySectionLoading` - Fired when section starts loading
- `lazySectionLoaded` - Fired when section finishes loading

Listen for these events to initialize additional functionality:

```javascript
document.addEventListener('lazySectionLoaded', function(e) {
    const section = e.detail.section;
    // Initialize carousels, animations, etc.
});
```

## Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge) - Full support
- Older browsers - Graceful degradation (sections load normally)
- Intersection Observer polyfill can be added if needed

## Notes

1. **main.js Compatibility**: The lazy loader works with existing main.js initialization. Product carousels are initialized when sections become visible.

2. **Above-the-Fold Content**: The hero section (`block-finder`) and features strip (`block-features`) load immediately as they're above the fold.

3. **Mobile Performance**: Especially beneficial on mobile devices with slower connections.

4. **SEO**: No negative impact - content is still in HTML, just hidden initially.

## Future Enhancements

Possible improvements:
- Add Intersection Observer polyfill for older browsers
- Implement image lazy loading for product images
- Add skeleton screens instead of spinner
- Preload sections when user hovers over navigation
- Add analytics tracking for section load times


