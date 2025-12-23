import os
import re
from pathlib import Path

def find_balanced_div_end(content, start_pos):
    """Find the position where a balanced div tag closes"""
    pos = start_pos
    depth = 0
    while pos < len(content):
        # Find next occurrence of <div or </div>
        next_open = content.find('<div', pos)
        next_close = content.find('</div>', pos)
        
        if next_open == -1 and next_close == -1:
            return -1
        
        if next_open != -1 and (next_close == -1 or next_open < next_close):
            depth += 1
            pos = next_open + 4
        else:
            depth -= 1
            pos = next_close + 6
            if depth == 0:
                return pos
    
    return -1

def remove_home_dropdown(file_path):
    """Remove dropdown from the خانه (Home) menu item"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        # Find the pattern: li tag with submenu classes containing home link
        # Look for the specific pattern: <li class="main-menu__item main-menu__item--submenu--menu main-menu__item--has-submenu">
        # followed by <a href="index.html" class="main-menu__link">خانه
        pattern = r'<li class="main-menu__item main-menu__item--submenu--menu main-menu__item--has-submenu">\s*<a href="index\.html" class="main-menu__link">خانه'
        
        match = re.search(pattern, content)
        if not match:
            return False
        
        start_pos = match.start()
        
        # Find where the <a> tag closes (after SVG)
        # Look for </svg></a> pattern
        link_end_pattern = r'</svg></a>'
        link_end_match = re.search(link_end_pattern, content[start_pos:])
        if not link_end_match:
            return False
        
        link_end_pos = start_pos + link_end_match.end()
        
        # Now find the submenu div start
        submenu_start_pattern = r'<div class="main-menu__submenu">'
        submenu_start_match = re.search(submenu_start_pattern, content[link_end_pos:])
        if not submenu_start_match:
            return False
        
        submenu_start_pos = link_end_pos + submenu_start_match.start()
        
        # Find where the submenu div closes (balanced tags)
        submenu_end_pos = find_balanced_div_end(content, submenu_start_pos)
        if submenu_end_pos == -1:
            return False
        
        # Find the closing </li> tag
        li_end_pos = content.find('</li>', submenu_end_pos)
        if li_end_pos == -1:
            return False
        li_end_pos += 5  # Include '</li>'
        
        # Extract indentation from the original li tag
        # Look backwards for newline and whitespace
        indent_start = content.rfind('\n', max(0, start_pos - 100), start_pos)
        if indent_start != -1:
            indent = content[indent_start + 1:start_pos]
        else:
            indent = '\t\t\t\t\t\t'
        
        # Reconstruct the content
        before = content[:start_pos]
        after = content[li_end_pos:]
        
        # New content: simple li with simple link
        new_content = f'{before}{indent}<li class="main-menu__item">\n{indent}\t<a href="index.html" class="main-menu__link">خانه</a>\n{indent}</li>{after}'
        
        if new_content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            return True
        return False
    except Exception as e:
        print(f"Error processing {file_path}: {e}")
        import traceback
        traceback.print_exc()
        return False

def main():
    # Get all HTML files in the current directory
    html_files = list(Path('.').glob('*.html'))
    
    modified_count = 0
    for html_file in html_files:
        if remove_home_dropdown(html_file):
            print(f"Modified: {html_file}")
            modified_count += 1
    
    print(f"\nTotal files modified: {modified_count}")

if __name__ == '__main__':
    main()
