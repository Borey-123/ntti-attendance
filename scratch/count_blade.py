import re

with open('resources/views/portal/index.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

stack = []

for i, line in enumerate(lines, 1):
    clean_line = re.sub(r'\{\{--.*?--\}\}', '', line)
    
    # Simple scanner for @if and @endif
    tokens = re.findall(r'@if\b|@endif\b', clean_line)
    for tok in tokens:
        if tok == '@if':
            stack.append(i)
        elif tok == '@endif':
            if stack:
                stack.pop()
            else:
                print(f"Unmatched @endif at line {i}")

print("Unclosed @if tags opened at lines:", stack)
