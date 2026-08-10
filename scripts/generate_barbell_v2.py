#!/usr/bin/env python3
"""Generate square barbell thumbnails with SVG-rendered sticker numbers.

Example:
    python3 scripts/generate_barbell_v2.py --size 400 --background '#172554' \
        --number 5 --output public/barbell-5.png
"""

from __future__ import annotations

import argparse
import subprocess
import tempfile
from pathlib import Path

from PIL import Image


DEFAULT_SOURCE = Path(__file__).resolve().parents[1] / "docs" / "bbell2_cropped.png"
NUMBER_GENERATOR = Path(__file__).resolve().parents[1] / "docs" / "sticker-number.py"
SUPERSAMPLE = 4
NUMBER_WIDTH_RATIO = 0.22
NUMBER_VIEWBOX_WIDTH = 108
NUMBER_VIEWBOX_HEIGHT = 155


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


def render_number(number: int, canvas_size: int) -> Image.Image:
    """Rasterize a tightly cropped SVG number image using macOS's SVG renderer."""
    with tempfile.TemporaryDirectory() as directory:
        svg = Path(directory) / "number.svg"
        png = Path(directory) / "number.png"
        width = round(canvas_size * NUMBER_WIDTH_RATIO)
        height = round(width * NUMBER_VIEWBOX_HEIGHT / NUMBER_VIEWBOX_WIDTH)

        try:
            subprocess.run(
                ["python3", str(NUMBER_GENERATOR), "--number", str(number), "--output", str(svg)],
                check=True,
                capture_output=True,
                text=True,
            )
            svg.write_text(
                svg.read_text(encoding="utf-8").replace(
                    '<svg xmlns="http://www.w3.org/2000/svg"',
                    f'<svg xmlns="http://www.w3.org/2000/svg" width="{width}" height="{height}"',
                    1,
                ),
                encoding="utf-8",
            )
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
    number: int,
) -> None:
    """Render a centered barbell and SVG sticker number in a square PNG."""
    if size < 1:
        raise ValueError("size must be at least 1")
    if number <= 0:
        raise ValueError("number must be greater than zero")

    render_size = size * SUPERSAMPLE
    barbell = Image.open(source).convert("RGBA")
    barbell.thumbnail((int(render_size * 0.96), render_size), Image.Resampling.LANCZOS)
    number_image = render_number(number, render_size)
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
    parser.add_argument("--number", type=int, default=1, help="positive number to display below the barbell")
    parser.add_argument("--source", type=Path, default=DEFAULT_SOURCE, help="source barbell PNG")
    args = parser.parse_args()

    generate_image(
        args.source,
        args.output,
        args.size,
        args.background,
        args.number,
    )


if __name__ == "__main__":
    main()
