#!/usr/bin/env python3
"""Generate exact-logo production assets for Al-Kutub Android and web."""

from __future__ import annotations

import base64
import io
from pathlib import Path
from typing import Iterable

from PIL import Image, ImageDraw


ROOT = Path(__file__).resolve().parents[1]

SOURCE_SYMBOL = Path(
    "/home/amiir/Downloads/Modern Kufi Logo Combining Tradition and Digital Design (7).png"
)
SOURCE_FULL = Path(
    "/home/amiir/Downloads/Modern Kufi Logo Combining Tradition and Digital Design (8).png"
)

ANDROID_RES = ROOT / "AlKutub/app/src/main/res"
ANDROID_EXPORT = ROOT / "brand-assets/al-kutub/android"
WEB_PUBLIC = ROOT / "al-kutub/public/assets/static/images/logo"
WEB_ROOT = ROOT / "al-kutub/public"
WEB_EXPORT = ROOT / "brand-assets/al-kutub/web"

GREEN = (126, 217, 87)
BLACK = (26, 26, 26)
WHITE = (255, 255, 255, 255)
LIGHT_SURFACE = (239, 239, 239, 255)
DARK_SURFACE = (16, 22, 17, 255)
SPINNER = (126, 217, 87, 255)


def ensure_parent(path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)


def save_png(image: Image.Image, path: Path) -> None:
    ensure_parent(path)
    image.save(path, format="PNG")


def save_webp(image: Image.Image, path: Path) -> None:
    ensure_parent(path)
    image.save(path, format="WEBP", lossless=True, method=6)


def save_ico(image: Image.Image, path: Path) -> None:
    ensure_parent(path)
    image.save(path, format="ICO", sizes=[(16, 16), (32, 32), (48, 48)])


def save_svg_with_png(image: Image.Image, path: Path) -> None:
    buffer = io.BytesIO()
    image.save(buffer, format="PNG")
    payload = base64.b64encode(buffer.getvalue()).decode("ascii")
    width, height = image.size
    svg = (
        f'<svg xmlns="http://www.w3.org/2000/svg" width="{width}" height="{height}" '
        f'viewBox="0 0 {width} {height}" fill="none">'
        f'<image width="{width}" height="{height}" href="data:image/png;base64,{payload}"/>'
        f"</svg>"
    )
    ensure_parent(path)
    path.write_text(svg, encoding="utf-8")


def load_image(path: Path) -> Image.Image:
    return Image.open(path).convert("RGB")


def find_art_bbox(image: Image.Image, near_white: int = 245, edge_ignore: int = 100) -> tuple[int, int, int, int]:
    width, height = image.size
    pixels = image.load()
    min_x, min_y = width, height
    max_x, max_y = 0, 0

    for y in range(height):
        for x in range(width):
            if x > width - edge_ignore and y < edge_ignore:
                continue
            r, g, b = pixels[x, y]
            if r > near_white and g > near_white and b > near_white:
                continue
            min_x = min(min_x, x)
            min_y = min(min_y, y)
            max_x = max(max_x, x)
            max_y = max(max_y, y)

    return min_x, min_y, max_x + 1, max_y + 1


def crop_with_padding(image: Image.Image, bbox: tuple[int, int, int, int], padding: int) -> Image.Image:
    left, top, right, bottom = bbox
    left = max(left - padding, 0)
    top = max(top - padding, 0)
    right = min(right + padding, image.size[0])
    bottom = min(bottom + padding, image.size[1])
    return image.crop((left, top, right, bottom))


def rgba_from_solid_over_white(image: Image.Image, base_colors: Iterable[tuple[int, int, int]]) -> Image.Image:
    output = Image.new("RGBA", image.size, (0, 0, 0, 0))
    source = image.convert("RGB")
    src_pixels = source.load()
    out_pixels = output.load()
    base_colors = list(base_colors)

    for y in range(source.size[1]):
        for x in range(source.size[0]):
            observed = src_pixels[x, y]
            if observed[0] > 250 and observed[1] > 250 and observed[2] > 250:
                continue

            base = min(
                base_colors,
                key=lambda color: (
                    (observed[0] - color[0]) ** 2
                    + (observed[1] - color[1]) ** 2
                    + (observed[2] - color[2]) ** 2
                ),
            )

            alpha_samples: list[float] = []
            for channel, src_channel in zip(observed, base):
                if src_channel >= 255:
                    continue
                sample = (255 - channel) / (255 - src_channel)
                alpha_samples.append(max(0.0, min(sample, 1.0)))

            alpha = int(round((sum(alpha_samples) / len(alpha_samples)) * 255)) if alpha_samples else 0
            if alpha < 5:
                continue
            out_pixels[x, y] = (*base, alpha)

    return trim_transparent(output)


def trim_transparent(image: Image.Image) -> Image.Image:
    alpha = image.getchannel("A")
    bbox = alpha.getbbox()
    return image.crop(bbox) if bbox else image


def make_text_white(image: Image.Image, threshold: int = 50) -> Image.Image:
    output = image.copy()
    pixels = output.load()
    for y in range(output.size[1]):
        for x in range(output.size[0]):
            r, g, b, a = pixels[x, y]
            if a > 0 and r < threshold and g < threshold and b < threshold:
                pixels[x, y] = (255, 255, 255, a)
    return output


def fit_inside_canvas(
    image: Image.Image,
    size: tuple[int, int],
    max_content_ratio: float,
    background: tuple[int, int, int, int] = (0, 0, 0, 0),
) -> Image.Image:
    canvas = Image.new("RGBA", size, background)
    target_width = int(size[0] * max_content_ratio)
    target_height = int(size[1] * max_content_ratio)
    fitted = image.copy()
    fitted.thumbnail((target_width, target_height), Image.Resampling.LANCZOS)
    offset = ((size[0] - fitted.size[0]) // 2, (size[1] - fitted.size[1]) // 2)
    canvas.alpha_composite(fitted, dest=offset)
    return canvas


def compose_icon(size: int, symbol: Image.Image, background: tuple[int, int, int, int], round_mask: bool = False) -> Image.Image:
    canvas = fit_inside_canvas(symbol, (size, size), max_content_ratio=0.58, background=background)
    if not round_mask:
        return canvas

    mask = Image.new("L", (size, size), 0)
    mask_draw = ImageDraw.Draw(mask)
    mask_draw.ellipse((0, 0, size - 1, size - 1), fill=255)
    rounded = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    rounded.paste(canvas, mask=mask)
    return rounded


def export_android_launchers(symbol: Image.Image) -> None:
    densities = {
        "mdpi": 48,
        "hdpi": 72,
        "xhdpi": 96,
        "xxhdpi": 144,
        "xxxhdpi": 192,
    }

    light_background = WHITE
    dark_background = DARK_SURFACE

    light_foreground = fit_inside_canvas(symbol, (512, 512), max_content_ratio=0.58)
    dark_foreground = light_foreground.copy()

    save_png(light_foreground, ANDROID_EXPORT / "light/adaptive-icon-foreground-512.png")
    save_png(Image.new("RGBA", (512, 512), light_background), ANDROID_EXPORT / "light/adaptive-icon-background-512.png")
    save_png(dark_foreground, ANDROID_EXPORT / "dark/adaptive-icon-foreground-512.png")
    save_png(Image.new("RGBA", (512, 512), dark_background), ANDROID_EXPORT / "dark/adaptive-icon-background-512.png")

    save_png(light_foreground, ANDROID_RES / "drawable/ic_launcher_foreground_source.png")
    save_png(dark_foreground, ANDROID_RES / "drawable-night/ic_launcher_foreground_source.png")
    save_png(fit_inside_canvas(symbol, (512, 512), max_content_ratio=0.48), ANDROID_RES / "drawable/ic_logo_source.png")

    for density, size in densities.items():
        save_webp(
            compose_icon(size, symbol, light_background, round_mask=False),
            ANDROID_RES / f"mipmap-{density}/ic_launcher.webp",
        )
        save_webp(
            compose_icon(size, symbol, light_background, round_mask=True),
            ANDROID_RES / f"mipmap-{density}/ic_launcher_round.webp",
        )
        save_png(
            compose_icon(size, symbol, light_background, round_mask=False),
            ANDROID_EXPORT / f"light/launcher-{size}.png",
        )
        save_png(
            compose_icon(size, symbol, dark_background, round_mask=False),
            ANDROID_EXPORT / f"dark/launcher-{size}.png",
        )
        save_png(
            compose_icon(size, symbol, light_background, round_mask=True),
            ANDROID_EXPORT / f"light/launcher-round-{size}.png",
        )
        save_png(
            compose_icon(size, symbol, dark_background, round_mask=True),
            ANDROID_EXPORT / f"dark/launcher-round-{size}.png",
        )


def export_web_assets(symbol: Image.Image, symbol_dark: Image.Image, full_light: Image.Image, full_dark: Image.Image) -> None:
    transparent_512 = fit_inside_canvas(symbol, (512, 512), max_content_ratio=0.56)
    transparent_192 = fit_inside_canvas(symbol, (192, 192), max_content_ratio=0.56)
    dark_512 = fit_inside_canvas(symbol_dark, (512, 512), max_content_ratio=1.0)
    dark_192 = fit_inside_canvas(symbol_dark, (192, 192), max_content_ratio=1.0)

    save_png(transparent_512, WEB_PUBLIC / "al-kutub-symbol.png")
    save_png(dark_512, WEB_PUBLIC / "al-kutub-symbol-dark.png")
    save_svg_with_png(transparent_512, WEB_PUBLIC / "al-kutub-symbol.svg")
    save_svg_with_png(dark_512, WEB_PUBLIC / "al-kutub-symbol-dark.svg")

    save_png(fit_inside_canvas(full_light, (1024, 512), max_content_ratio=0.88), WEB_PUBLIC / "al-kutub-full.png")
    save_png(fit_inside_canvas(full_dark, (1024, 512), max_content_ratio=0.88), WEB_PUBLIC / "al-kutub-full-dark.png")
    save_svg_with_png(fit_inside_canvas(full_light, (1024, 512), max_content_ratio=0.88), WEB_PUBLIC / "al-kutub-full.svg")
    save_svg_with_png(fit_inside_canvas(full_dark, (1024, 512), max_content_ratio=0.88), WEB_PUBLIC / "al-kutub-full-dark.svg")
    save_svg_with_png(fit_inside_canvas(full_light, (1024, 512), max_content_ratio=0.88), WEB_PUBLIC / "logo.svg")

    save_png(transparent_512, WEB_PUBLIC / "favicon-source-512.png")
    save_png(transparent_192, WEB_PUBLIC / "favicon-192.png")
    save_png(dark_192, WEB_PUBLIC / "favicon-dark-192.png")
    save_png(fit_inside_canvas(symbol, (32, 32), max_content_ratio=0.56), WEB_PUBLIC / "favicon.png")
    save_png(fit_inside_canvas(symbol_dark, (32, 32), max_content_ratio=1.0), WEB_PUBLIC / "favicon-dark.png")
    save_svg_with_png(transparent_512, WEB_PUBLIC / "favicon.svg")
    save_svg_with_png(dark_512, WEB_PUBLIC / "favicon-dark.svg")
    save_ico(fit_inside_canvas(symbol, (64, 64), max_content_ratio=0.56), WEB_ROOT / "favicon.ico")

    save_png(fit_inside_canvas(full_light, (1024, 560), max_content_ratio=0.76), WEB_PUBLIC / "splash-logo.png")
    save_png(fit_inside_canvas(full_dark, (1024, 560), max_content_ratio=0.76), WEB_PUBLIC / "splash-logo-dark.png")

    save_png(transparent_512, WEB_EXPORT / "icon-only-light-512.png")
    save_png(dark_512, WEB_EXPORT / "icon-only-dark-512.png")
    save_png(fit_inside_canvas(full_light, (512, 512), max_content_ratio=0.86), WEB_EXPORT / "full-logo-light-512.png")
    save_png(fit_inside_canvas(full_dark, (512, 512), max_content_ratio=0.86), WEB_EXPORT / "full-logo-dark-512.png")
    save_svg_with_png(transparent_512, WEB_EXPORT / "icon-only-light.svg")
    save_svg_with_png(dark_512, WEB_EXPORT / "icon-only-dark.svg")
    save_svg_with_png(fit_inside_canvas(full_light, (512, 512), max_content_ratio=0.86), WEB_EXPORT / "full-logo-light.svg")
    save_svg_with_png(fit_inside_canvas(full_dark, (512, 512), max_content_ratio=0.86), WEB_EXPORT / "full-logo-dark.svg")


def main() -> None:
    symbol_source = load_image(SOURCE_SYMBOL)
    full_source = load_image(SOURCE_FULL)

    symbol_crop = crop_with_padding(symbol_source, find_art_bbox(symbol_source), padding=28)
    full_crop = crop_with_padding(full_source, find_art_bbox(full_source), padding=44)

    symbol_transparent = rgba_from_solid_over_white(symbol_crop, [GREEN])
    full_transparent = rgba_from_solid_over_white(full_crop, [GREEN, BLACK])
    full_transparent_white_text = make_text_white(full_transparent)

    symbol_dark = fit_inside_canvas(symbol_transparent, (512, 512), max_content_ratio=0.56)
    full_dark = fit_inside_canvas(full_transparent_white_text, (1024, 560), max_content_ratio=0.76)
    full_light = fit_inside_canvas(full_transparent, (1024, 560), max_content_ratio=0.76)

    export_android_launchers(symbol_transparent)
    export_web_assets(symbol_transparent, symbol_dark, full_light, full_dark)

    save_png(full_light, ANDROID_RES / "drawable/splash_logo_light.png")
    save_png(full_dark, ANDROID_RES / "drawable/splash_logo_dark.png")
    save_png(fit_inside_canvas(symbol_transparent, (512, 512), max_content_ratio=0.56), ANDROID_EXPORT / "shared/icon-only-light-512.png")
    save_png(symbol_dark, ANDROID_EXPORT / "shared/icon-only-dark-512.png")
    save_png(fit_inside_canvas(full_light, (512, 512), max_content_ratio=0.86), ANDROID_EXPORT / "shared/full-logo-light-512.png")
    save_png(fit_inside_canvas(full_dark, (512, 512), max_content_ratio=0.86), ANDROID_EXPORT / "shared/full-logo-dark-512.png")

    print("Generated Al-Kutub brand assets.")


if __name__ == "__main__":
    main()
