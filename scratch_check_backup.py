import os

path = "/run/media/tatsuya/F5E2-8EB4/backup/backupdata.dat"
if not os.path.exists(path):
    print("File not found.")
    exit(1)

with open(path, "rb") as f:
    data = f.read()

# Search for any occurrence of "Nurhasan" in case-insensitive way
term = b"nurhasan"
data_lower = data.lower()
pos = data_lower.find(term)
if pos != -1:
    print(f"Found 'nurhasan' (lowercase match) at position {pos}")
    # Print 50 bytes before and after
    start = max(0, pos - 20)
    end = min(len(data), pos + len(term) + 20)
    context = data[start:end]
    print(f"Raw Context hex: {context.hex()}")
    print(f"Raw Context ASCII: {repr(context)}")
else:
    print("Sub-string 'nurhasan' not found in backupdata.dat.")

# Search for "22078" or "22708" in string format
for pin in [b"22078", b"22708"]:
    pos = data.find(pin)
    if pos != -1:
         print(f"Found PIN string {pin.decode()} at position {pos}")
