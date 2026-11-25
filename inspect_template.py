import pptx

prs = pptx.Presentation('TEMPLATE PPT TUGAS AKHIR - FASILKOM.pptx')
print(f"Total Slides: {len(prs.slides)}")
print(f"Dimensions: {prs.slide_width.inches}\" x {prs.slide_height.inches}\" (16:9 widescreen 20\"x11.25\")")

for i, slide in enumerate(prs.slides):
    print(f"\n--- SLIDE {i+1} --- (layout: {slide.slide_layout.name})")
    for s_idx, shape in enumerate(slide.shapes):
        shape_info = f"  [{s_idx}] type={shape.shape_type}, name='{shape.name}', pos=({shape.left}, {shape.top}, {shape.width}, {shape.height})"
        if shape.has_text_frame:
            texts = [p.text.strip() for p in shape.text_frame.paragraphs if p.text.strip()]
            print(f"{shape_info} -> text: {' // '.join(texts)}")
        else:
            print(shape_info)
