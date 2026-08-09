#!/usr/bin/env python3
"""Generate square barbell thumbnails with SVG-rendered sticker numbers.

Example:
    python3 scripts/generate_barbell_v2.py --size 400 --background '#172554' \
        --number 5 --output public/barbell-5.png
"""

from __future__ import annotations

import argparse
import html
import subprocess
import tempfile
from pathlib import Path

from PIL import Image


DEFAULT_SOURCE = Path(__file__).resolve().parents[1] / "docs" / "bbell2_cropped.png"
SUPERSAMPLE = 4


def parse_color(value: str) -> tuple[int, int, int, int] | None:
    """Parse a CSS-style #RRGGBB or #RGB color, or transparent."""
    if value.lower() == "transparent":
        return None

    value = value.removeprefix("#")

    if len(value) == 3:
        value = "".join(channel * 2 for channel in value)

    if len(value) != 6:
        raise argparse.ArgumentTypeError("colors must use #RGB or #RRGGBB")

    try:
        return (*tuple(int(value[index : index + 2], 16) for index in range(0, 6, 2)), 255)
    except ValueError as error:
        raise argparse.ArgumentTypeError("colors must use hexadecimal digits") from error


def build_number_svg(number: str, canvas_size: int, font_family: str) -> str:
    """Build the SVG for a white number with black and white sticker outlines."""
    font_size = int(canvas_size * 0.29)
    black_stroke_width = max(8, canvas_size // 52)
    white_stroke_width = black_stroke_width * 3
    text = html.escape(number)
    center = canvas_size // 2
    baseline = int(center + font_size * 0.35)

    return f'''<svg xmlns="http://www.w3.org/2000/svg" width="{canvas_size}" height="{canvas_size}" viewBox="0 0 {canvas_size} {canvas_size}">
  <text x="{center}" y="{baseline}" text-anchor="middle" font-family="{html.escape(font_family)}" font-size="{font_size}" font-weight="900" fill="white" stroke="white" stroke-width="{white_stroke_width}" stroke-linejoin="round" paint-order="stroke">{text}</text>
  <text x="{center}" y="{baseline}" text-anchor="middle" font-family="{html.escape(font_family)}" font-size="{font_size}" font-weight="900" fill="white" stroke="black" stroke-width="{black_stroke_width}" stroke-linejoin="round" paint-order="stroke">{text}</text>
</svg>'''


def render_number(number: str, canvas_size: int, font_family: str) -> Image.Image:
    """Rasterize a tightly cropped SVG number image using macOS's SVG renderer."""
    with tempfile.TemporaryDirectory() as directory:
        svg = Path(directory) / "number.svg"
        png = Path(directory) / "number.png"
        svg.write_text(build_number_svg(number, canvas_size, font_family), encoding="utf-8")

        try:
            subprocess.run(
                ["sips", "-s", "format", "png", str(svg), "--out", str(png)],
                check=True,
                capture_output=True,
                text=True,
            )
        except FileNotFoundError as error:
            raise RuntimeError("SVG rasterization requires macOS's sips command") from error

        with Image.open(png) as image:
            rendered = image.convert("RGBA")

    bounds = rendered.getbbox()
    if bounds is None:
        raise ValueError("number did not render")

    return rendered.crop(bounds)


def generate_image(
    source: Path,
    output: Path,
    size: int,
    background: tuple[int, int, int, int] | None,
    number: str,
    font_family: str = ".SF Rounded Numeric, Arial Rounded MT Bold, Arial, sans-serif",
) -> None:
    """Render a centered barbell and SVG sticker number in a square PNG."""
    if size < 1:
        raise ValueError("size must be at least 1")
    if not number:
        raise ValueError("number cannot be empty")

    render_size = size * SUPERSAMPLE
    barbell = Image.open(source).convert("RGBA")
    barbell.thumbnail((int(render_size * 0.96), render_size), Image.Resampling.LANCZOS)
    number_image = render_number(number, render_size, font_family)
    gap = max(4, round(render_size * 0.10))
    group_height = barbell.height + gap + number_image.height
    group_top = (render_size - group_height) // 2

    canvas = Image.new("RGBA", (render_size, render_size), background or (0, 0, 0, 0))
    canvas.alpha_composite(barbell, ((render_size - barbell.width) // 2, group_top))
    canvas.alpha_composite(
        number_image,
        ((render_size - number_image.width) // 2, group_top + barbell.height + gap),
    )

    output.parent.mkdir(parents=True, exist_ok=True)
    canvas.resize((size, size), Image.Resampling.LANCZOS).save(output, "PNG")


def main() -> None:
    parser = argparse.ArgumentParser(description="Generate a square barbell PNG with an SVG sticker number.")
    parser.add_argument("--output", type=Path, required=True, help="destination PNG path")
    parser.add_argument("--size", type=int, default=400, help="square image dimension in pixels")
    parser.add_argument("--background", type=parse_color, default=None, help="hex color or transparent")
    parser.add_argument("--number", default="1", help="text to display below the barbell")
    parser.add_argument("--source", type=Path, default=DEFAULT_SOURCE, help="source barbell PNG")
    parser.add_argument("--font-family", default=".SF Rounded Numeric, Arial Rounded MT Bold, Arial, sans-serif")
    args = parser.parse_args()

    generate_image(
        args.source,
        args.output,
        args.size,
        args.background,
        args.number,
        args.font_family,
    )


if __name__ == "__main__":
    main()
