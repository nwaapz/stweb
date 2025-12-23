import re
import os
from pathlib import Path

# Pattern to match product-card__rating-label divs (both single-line and multi-line)
# This matches:
# - Single line: <div class="product-card__rating-label">...</div>
# - Multi-line: <div class="product-card__rating-label">...
#                </div>
# The pattern uses non-greedy matching to handle both cases
pattern = r'<div\s+class="product-card__rating-label">.*?</div>'

def remove_rating_labels(file_path):
    """Remove all product-card__rating-label divs from a file."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Count occurrences before removal
        before_count = len(re.findall(pattern, content, re.DOTALL))
        
        if before_count > 0:
            # Remove all instances (using DOTALL flag to match across lines)
            new_content = re.sub(pattern, '', content, flags=re.DOTALL)
            
            # Clean up any double newlines that might result
            new_content = re.sub(r'\n\s*\n\s*\n', '\n\n', new_content)
            
            # Write back
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            
            return before_count
        return 0
    except Exception as e:
        print(f"Error processing {file_path}: {e}")
        return 0

def main():
    # Get all HTML files in the current directory
    html_files = list(Path('.').glob('*.html'))
    
    total_removed = 0
    files_modified = 0
    
    for html_file in html_files:
        count = remove_rating_labels(html_file)
        if count > 0:
            files_modified += 1
            total_removed += count
            print(f"Removed {count} instance(s) from {html_file}")
    
    print(f"\nDone! Modified {files_modified} file(s), removed {total_removed} total instance(s)")

if __name__ == '__main__':
    main()

