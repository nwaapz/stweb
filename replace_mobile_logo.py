import re

# Read the file
with open('index.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Pattern to match the mobile logo section from opening tag to closing tag
# Match from <a class="mobile-header__logo" to </a> after <!-- mobile-logo / end -->
# Use non-greedy match and handle both single-line and multi-line
pattern = r'<a class="mobile-header__logo" href="">.*?<!-- mobile-logo / end --></a>'

# Replacement with image logo
replacement = '<a class="mobile-header__logo" href="index.html"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;"></a>'

# Replace using regex with DOTALL flag to match across newlines
new_content = re.sub(pattern, replacement, content, flags=re.DOTALL)

# Check if replacement was made
if new_content != content:
    # Write back to file
    with open('index.html', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Mobile logo replaced successfully!")
else:
    print("Pattern not found. Trying alternative approach...")
    # Try a more specific pattern - find the exact SVG content
    # Match from <!-- mobile-logo --> to <!-- mobile-logo / end -->
    pattern2 = r'<a class="mobile-header__logo" href="">.*?<!-- mobile-logo -->.*?<!-- mobile-logo / end --></a>'
    new_content = re.sub(pattern2, replacement, content, flags=re.DOTALL)
    
    if new_content != content:
        with open('index.html', 'w', encoding='utf-8') as f:
            f.write(new_content)
        print("Mobile logo replaced successfully with alternative pattern!")
    else:
        print("Failed to find mobile logo pattern. Please check the file manually.")

