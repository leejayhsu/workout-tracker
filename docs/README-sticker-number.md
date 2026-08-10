# Sticker number SVG generator

This dependency-free Python script generates rounded numbers **1** through **9** as transparent SVGs. Number 1 uses the reference-image vector path; the remaining numbers use a rounded system font. The result has three vector layers:

1. an outer white sticker border;
2. a dark inner outline;
3. an editable solid fill.

Run it with Python 3:

```bash
python3 sticker-number.py --number 1
```

Change the number's fill color:

```bash
python3 sticker-number.py --number 2 --fill '#ffcf3f' --output yellow-number-2.svg
```

All options:

```bash
python3 sticker-number.py \
  --number 1 \
  --fill '#ffffff' \
  --black '#142b43' \
  --outer '#ffffff' \
  --inner-width 11 \
  --outer-width 24 \
  --output number-1.svg
```

You can also edit `--number-fill` near the top of the generated SVG directly. The SVG uses a `viewBox`, so it scales cleanly to any size and has no background rectangle.
