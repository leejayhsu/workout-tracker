#!/usr/bin/env python3
"""Generate square barbell thumbnails from docs/bbell2_cropped.png.

Example:
    python3 scripts/generate_barbell.py --size 200 --background '#172554' \
        --number 5 --number-color '#ffffff' --output public/barbell-5.png
"""

from __future__ import annotations

import argparse
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


DEFAULT_SOURCE = Path(__file__).resolve().parents[1] / "docs" / "bbell2_cropped.png"
FONT_CANDIDATES = (
    "/System/Library/Fonts/Supplemental/Arial Rounded Bold.ttf",
    "/System/Library/Fonts/Supplemental/Arial Bold.ttf",
    "/Library/Fonts/Arial Bold.ttf",
    "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf",
)
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


def load_font(font_path: str | None, size: int) -> ImageFont.FreeTypeFont:
    """Load a scalable bold font, with common platform defaults."""
    candidates = (font_path,) if font_path else FONT_CANDIDATES

    for candidate in candidates:
        if candidate and Path(candidate).is_file():
            return ImageFont.truetype(candidate, size)

    raise FileNotFoundError("No bold TrueType font found; provide one with --font /path/to/font.ttf")


def render_number(
    number: str,
    color: tuple[int, int, int, int],
    canvas_size: int,
    font_path: str | None,
) -> Image.Image:
    """Render a tightly cropped, outlined number image."""
    font_size = int(canvas_size * 0.29)
    stroke_width = max(2, canvas_size // 65)

    while True:
        font = load_font(font_path, font_size)
        box = ImageDraw.Draw(Image.new("RGBA", (1, 1))).textbbox(
            (0, 0), number, font=font, stroke_width=stroke_width
        )
        if box[2] - box[0] <= canvas_size * 0.82 or font_size == 1:
            break
        font_size -= 1

    image = Image.new("RGBA", (box[2] - box[0], box[3] - box[1]))
    draw = ImageDraw.Draw(image)
    draw.text(
        (-box[0], -box[1]),
        number,
        fill=color,
        font=font,
        stroke_width=stroke_width,
        stroke_fill=(0, 0, 0, 255),
    )

    return image


def generate_image(
    source: Path,
    output: Path,
    size: int,
    background: tuple[int, int, int, int] | None,
    number: str,
    number_color: tuple[int, int, int, int],
    font_path: str | None = None,
) -> None:
    """Render a centered barbell and number in a square PNG."""
    if size < 1:
        raise ValueError("size must be at least 1")
    if not number:
        raise ValueError("number cannot be empty")
    if number_color is None:
        raise ValueError("number color cannot be transparent")

    render_size = size * SUPERSAMPLE
    barbell = Image.open(source).convert("RGBA")
    barbell.thumbnail((int(render_size * 0.96), render_size), Image.Resampling.LANCZOS)
    number_image = render_number(number, number_color, render_size, font_path)
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
    parser = argparse.ArgumentParser(description="Generate a square barbell PNG.")
    parser.add_argument("--output", type=Path, required=True, help="destination PNG path")
    parser.add_argument("--size", type=int, default=200, help="square image dimension in pixels")
    parser.add_argument("--background", type=parse_color, default=None, help="hex color or transparent")
    parser.add_argument("--number", default="1", help="text to display below the barbell")
    parser.add_argument("--number-color", type=parse_color, default=parse_color("#ffffff"))
    parser.add_argument("--source", type=Path, default=DEFAULT_SOURCE, help="source barbell PNG")
    parser.add_argument("--font", help="path to a bold TrueType font")
    args = parser.parse_args()

    generate_image(
        args.source,
        args.output,
        args.size,
        args.background,
        args.number,
        args.number_color,
        args.font,
    )


if __name__ == "__main__":
    main()
