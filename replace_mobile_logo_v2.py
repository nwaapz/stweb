import re
import sys

# Read the file
try:
    with open('index.html', 'r', encoding='utf-8') as f:
        content = f.read()
except Exception as e:
    print(f"Error reading file: {e}")
    sys.exit(1)

# Find the position of the mobile logo opening tag
start_marker = '<a class="mobile-header__logo" href="">'
end_marker = '<!-- mobile-logo / end --></a>'

# Find start and end positions
start_pos = content.find(start_marker)
if start_pos == -1:
    print("Start marker not found!")
    sys.exit(1)

print(f"Start marker found at position: {start_pos}")

end_pos = content.find(end_marker, start_pos)
if end_pos == -1:
    print("End marker not found!")
    sys.exit(1)

print(f"End marker found at position: {end_pos}")

# Include the end marker in the replacement
end_pos += len(end_marker)

# Extract the part to replace
old_logo = content[start_pos:end_pos]
print(f"Old logo length: {len(old_logo)}")
print(f"Old logo preview (first 100 chars): {old_logo[:100]}")

# Create new logo
new_logo = '<a class="mobile-header__logo" href="index.html"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;"></a>'

# Replace
new_content = content[:start_pos] + new_logo + content[end_pos:]

# Write back
try:
    with open('index.html', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f"Mobile logo replaced successfully!")
    print(f"New logo length: {len(new_logo)}")
except Exception as e:
    print(f"Error writing file: {e}")
    sys.exit(1)

