from __future__ import annotations

import importlib.util
import tempfile
import unittest
from pathlib import Path

from PIL import Image


SCRIPT = Path(__file__).with_name("generate_barbell_v2.py")
SPEC = importlib.util.spec_from_file_location("generate_barbell_v2", SCRIPT)
assert SPEC and SPEC.loader
generator = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(generator)


class GenerateBarbellV2Test(unittest.TestCase):
    def test_builds_an_svg_with_black_and_white_sticker_strokes(self) -> None:
        svg = generator.build_number_svg("12", 400, "Arial")

        self.assertIn('fill="white" stroke="black"', svg)
        self.assertIn('fill="white" stroke="white"', svg)
        self.assertIn('font-weight="900"', svg)

    def test_generates_a_square_png_with_the_requested_background(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            output = Path(directory) / "barbell.png"
            generator.generate_image(
                generator.DEFAULT_SOURCE,
                output,
                100,
                generator.parse_color("#123456"),
                "12",
            )

            with Image.open(output) as image:
                self.assertEqual(image.size, (100, 100))
                self.assertEqual(image.mode, "RGBA")
                self.assertEqual(image.getpixel((0, 99)), (18, 52, 86, 255))

    def test_generates_a_transparent_png_when_no_background_is_requested(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            output = Path(directory) / "barbell.png"
            generator.generate_image(
                generator.DEFAULT_SOURCE,
                output,
                100,
                None,
                "3",
            )

            with Image.open(output) as image:
                self.assertEqual(image.getpixel((0, 99))[3], 0)


if __name__ == "__main__":
    unittest.main()
