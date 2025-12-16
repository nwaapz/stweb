import sys

# Read the file
try:
    with open('index.html', 'r', encoding='utf-8') as f:
        content = f.read()
    print(f"File read successfully. Length: {len(content)}")
except Exception as e:
    print(f"ERROR reading file: {e}")
    sys.exit(1)

# Find the exact boundaries
start = '<a class="mobile-header__logo" href="">'
end = '<!-- mobile-logo / end --></a>'

start_idx = content.find(start)
if start_idx == -1:
    print("ERROR: Start marker not found")
    sys.exit(1)
print(f"Start found at index: {start_idx}")

end_idx = content.find(end, start_idx)
if end_idx == -1:
    print("ERROR: End marker not found")
    sys.exit(1)
print(f"End found at index: {end_idx}")

end_idx += len(end)
print(f"Replacement range: {start_idx} to {end_idx}")

# Replace
new_logo = '<a class="mobile-header__logo" href="index.html"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;"></a>'
result = content[:start_idx] + new_logo + content[end_idx:]

# Write
try:
    with open('index.html', 'w', encoding='utf-8') as f:
        f.write(result)
    print("SUCCESS: Mobile logo replaced")
except Exception as e:
    print(f"ERROR writing file: {e}")
    sys.exit(1)

