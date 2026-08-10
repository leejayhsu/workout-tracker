#!/usr/bin/env python3
"""Generate a scalable, transparent SVG of a sticker-style number."""

from __future__ import annotations

import argparse
from html import escape
from pathlib import Path


FONT_FAMILY = "Arial Rounded MT Bold, Arial, sans-serif"


def positive_number(value: str) -> float:
    number = float(value)
    if number <= 0:
        raise argparse.ArgumentTypeError("must be greater than zero")
    return number


def positive_integer(value: str) -> int:
    number = int(value)
    if number <= 0:
        raise argparse.ArgumentTypeError("must be greater than zero")
    return number


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Generate a rounded sticker-style positive number as an SVG."
    )
    parser.add_argument(
        "--number", type=positive_integer, default=1, help="number to generate"
    )
    parser.add_argument("--fill", default="#ffffff", help="number fill color")
    parser.add_argument(
        "--black", default="#142b43", help="dark inner outline color"
    )
    parser.add_argument("--outer", default="#ffffff", help="outer border color")
    parser.add_argument(
        "--inner-width", type=positive_number, default=11, help="inner stroke width"
    )
    parser.add_argument(
        "--outer-width", type=positive_number, default=24, help="outer stroke width"
    )
    parser.add_argument(
        "--output", type=Path, help="output SVG path"
    )
    args = parser.parse_args()
    if args.outer_width <= args.inner_width:
        parser.error("--outer-width must be greater than --inner-width")
    if args.output is None:
        args.output = Path(f"number-{args.number}.svg")

    return args


def make_svg(
    *, number: int, fill: str, black: str, outer: str, inner_width: float, outer_width: float
) -> str:
    fill = escape(fill, quote=True)
    black = escape(black, quote=True)
    outer = escape(outer, quote=True)

    numeral = numeral_svg(number)

    return f'''<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 108 155" role="img" aria-labelledby="title desc">
  <title id="title">Sticker-style number {number}</title>
  <desc id="desc">A rounded number {number} with an editable fill, dark inner outline, and white outer outline.</desc>
  <!-- Change the number-fill CSS variable below, or regenerate with the fill command-line option. -->
  <style>
    :root {{ --number-fill: {fill}; --inner-stroke: {black}; --outer-stroke: {outer}; }}
    .outline {{ stroke-linejoin: round; stroke-linecap: round; }}
  </style>
{numeral.format(inner_width=inner_width, outer_width=outer_width)}
</svg>
'''


def numeral_svg(number: int) -> str:
    font_size = {1: 130, 2: 70}.get(len(str(number)), 50)
    baseline = round(77 + font_size * 0.35)

    return f'''  <text class="outline" x="54" y="{baseline}" text-anchor="middle" font-family="{FONT_FAMILY}" font-size="{font_size}" font-weight="900" fill="var(--outer-stroke)" stroke="var(--outer-stroke)" stroke-width="{{outer_width:g}}">{number}</text>
  <text class="outline" x="54" y="{baseline}" text-anchor="middle" font-family="{FONT_FAMILY}" font-size="{font_size}" font-weight="900" fill="var(--number-fill)" stroke="var(--inner-stroke)" stroke-width="{{inner_width:g}}">{number}</text>
  <text x="54" y="{baseline}" text-anchor="middle" font-family="{FONT_FAMILY}" font-size="{font_size}" font-weight="900" fill="var(--number-fill)">{number}</text>'''


def main() -> None:
    args = parse_args()
    svg = make_svg(
        number=args.number,
        fill=args.fill,
        black=args.black,
        outer=args.outer,
        inner_width=args.inner_width,
        outer_width=args.outer_width,
    )
    args.output.write_text(svg, encoding="utf-8")
    print(f"Wrote {args.output}")


if __name__ == "__main__":
    main()
