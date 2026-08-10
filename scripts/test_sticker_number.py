from __future__ import annotations

import importlib.util
import unittest
from pathlib import Path


SCRIPT = Path(__file__).parents[1] / "docs" / "sticker-number.py"
SPEC = importlib.util.spec_from_file_location("sticker_number", SCRIPT)
assert SPEC and SPEC.loader
generator = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(generator)


class StickerNumberTest(unittest.TestCase):
    def test_one_uses_the_same_font_based_numeral_as_other_numbers(self) -> None:
        one = generator.numeral_svg(1)
        three = generator.numeral_svg(3)

        self.assertIn('<text class="outline"', one)
        self.assertNotIn("<path", one)
        self.assertEqual(one.count("font-family="), three.count("font-family="))

    def test_scales_three_digit_numbers_to_fit_the_sticker(self) -> None:
        self.assertIn('font-size="50"', generator.numeral_svg(200))


if __name__ == "__main__":
    unittest.main()
