"""
Busia Chicken Website - Image Optimization Script
Converts and compresses all large images to WebP + optimized JPEG/PNG fallbacks.
Run from project root: python tools/optimize_images.py
"""

import os
import sys
from pathlib import Path
from PIL import Image, ImageOps

IMG_DIR = Path(__file__).parent.parent / "Frontend" / "images"

# Max dimensions for different image types
MAX_HERO_W = 1600
MAX_CARD_W = 800
MAX_LOGO_W = 240

# Quality settings
WEBP_QUALITY = 82
JPEG_QUALITY = 82

def human_size(path):
    b = Path(path).stat().st_size
    return f"{b/1024:.1f} KB" if b < 1024*1024 else f"{b/1024/1024:.2f} MB"

def save_optimized(img, out_path, fmt, quality=85, **kwargs):
    """Save image with format-specific options."""
    out_path = Path(out_path)
    if fmt == "WEBP":
        img.save(out_path, "WEBP", quality=quality, method=6, **kwargs)
    elif fmt == "JPEG":
        img.save(out_path, "JPEG", quality=quality, optimize=True, progressive=True, **kwargs)
    elif fmt == "PNG":
        img.save(out_path, "PNG", optimize=True, **kwargs)
    return out_path

def process_image(src_name, max_w, is_hero=False):
    """Process one image: resize, convert to WebP + JPEG fallback."""
    src = IMG_DIR / src_name
    if not src.exists():
        print(f"  [SKIP] Not found: {src_name}")
        return

    orig_size = human_size(src)
    img = Image.open(src)
    orig_mode = img.mode

    # Convert palette/RGBA to RGB (safely — check for real alpha)
    has_alpha = img.mode in ("RGBA", "LA", "P")
    if has_alpha:
        # Check if any pixel has non-255 alpha
        if img.mode == "P":
            img = img.convert("RGBA")
        if img.mode in ("RGBA", "LA"):
            alpha = img.getchannel("A")
            min_alpha = alpha.getextrema()[0]
            has_real_alpha = min_alpha < 250
        else:
            has_real_alpha = False

        if not has_real_alpha:
            # No real transparency — safe to drop alpha, use white background
            bg = Image.new("RGB", img.size, (255, 255, 255))
            if img.mode in ("RGBA", "LA"):
                bg.paste(img, mask=img.getchannel("A"))
            else:
                bg.paste(img)
            img = bg
        # else keep alpha for WebP (lossy RGBA WebP)

    elif img.mode != "RGB":
        img = img.convert("RGB")

    # Resize if oversized
    w, h = img.size
    if w > max_w:
        ratio = max_w / w
        new_h = int(h * ratio)
        img = img.resize((max_w, new_h), Image.LANCZOS)
        new_w, new_h = img.size
    else:
        new_w, new_h = w, h

    # Stem name (slugified — remove spaces, special chars)
    stem = src.stem.lower().replace(" ", "-").replace("(", "").replace(")", "").replace(".", "")
    ext = src.suffix.lower()

    # Output paths
    webp_name = f"{stem}.webp"
    webp_path = IMG_DIR / webp_name

    # Save WebP
    save_optimized(img, webp_path, "WEBP", quality=WEBP_QUALITY)
    webp_size = human_size(webp_path)

    # Save JPEG fallback (only if source was JPEG or large PNG without alpha)
    if ext in (".jpg", ".jpeg") or (ext == ".png" and not has_alpha):
        jpg_name = f"{stem}-opt.jpg" if ext in (".jpg", ".jpeg") else f"{stem}.jpg"
        jpg_path = IMG_DIR / jpg_name
        rgb_img = img.convert("RGB") if img.mode != "RGB" else img
        save_optimized(rgb_img, jpg_path, "JPEG", quality=JPEG_QUALITY)
        jpg_size = human_size(jpg_path)
        fallback_info = f"JPEG fallback: {jpg_size}"
    else:
        fallback_info = "(PNG/WebP only)"

    print(f"  ✓ {src_name} ({orig_size}, {orig_mode} {w}x{h})")
    print(f"    → WebP: {webp_name} ({webp_size}),  {fallback_info}")
    print(f"    → Resized to: {new_w}x{new_h}")

def optimize_logo():
    """Optimize the logo PNG to a small version."""
    src = IMG_DIR / "logo.png"
    if not src.exists():
        # Try alternatives
        for alt in ["busia logo.png", "busia_chicken_logo.png"]:
            if (IMG_DIR / alt).exists():
                src = IMG_DIR / alt
                break
    if not src.exists():
        print("  [SKIP] logo.png not found")
        return

    orig_size = human_size(src)
    img = Image.open(src).convert("RGBA")

    # Resize to max 240px wide
    w, h = img.size
    if w > MAX_LOGO_W:
        ratio = MAX_LOGO_W / w
        img = img.resize((MAX_LOGO_W, int(h * ratio)), Image.LANCZOS)

    # Save optimized PNG (quantize to reduce size)
    out_png = IMG_DIR / "logo-sm.png"
    img.save(out_png, "PNG", optimize=True)

    # Also save as WebP
    out_webp = IMG_DIR / "logo-sm.webp"
    img.save(out_webp, "WEBP", quality=90, method=6)

    print(f"  ✓ logo.png ({orig_size}) → logo-sm.png ({human_size(out_png)}), logo-sm.webp ({human_size(out_webp)})")

def main():
    print("=" * 60)
    print("Busia Chicken Image Optimizer")
    print("=" * 60)

    # === HERO / LARGE JPEG IMAGES ===
    print("\n[1/4] Hero & Product JPEGs (max 1600px wide):")
    hero_jpegs = [
        "broiler-chickens.jpg",
        "farm-fresh-eggs.jpg",
        "layer-hens-eggs.jpg",
        "poultry-feed.jpg",
        "farm-exterior.jpg",
        "day-old-chicks.jpg",
    ]
    for name in hero_jpegs:
        process_image(name, MAX_HERO_W, is_hero=True)

    # === LARGE PNGs (bg images / slider images) ===
    print("\n[2/4] Background & Slider PNGs (max 1600px wide):")
    bg_pngs = [
        "adbg.png",
        "download (4).png",
        "download (7).png",
        "download (8).png",
        "download (5).png",
        "download (2).png",
        "download (3).png",
    ]
    for name in bg_pngs:
        process_image(name, MAX_HERO_W)

    # === PRODUCT / FEED PNGs ===
    print("\n[3/4] Product PNGs (max 800px wide):")
    product_pngs = [
        "Growers Mash.png",
        "Chick Starter Crumbs.png",
        "kienyeji mash.png",
    ]
    for name in product_pngs:
        process_image(name, MAX_CARD_W)

    # === LOGO ===
    print("\n[4/4] Logo optimization:")
    optimize_logo()

    print("\n" + "=" * 60)
    print("DONE. All optimized files written to Frontend/images/")
    print("=" * 60)

    # Summary of savings
    print("\nFinal image folder:")
    sizes = []
    for f in sorted(IMG_DIR.glob("*.*")):
        if f.is_file():
            sizes.append((f.stat().st_size, f.name))
    for sz, nm in sorted(sizes, reverse=True)[:20]:
        print(f"  {nm:45s} {sz/1024:>8.1f} KB")

if __name__ == "__main__":
    main()
