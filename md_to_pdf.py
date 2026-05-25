"""Convierte DEFENSA_CELULAR.md a PDF estilizado para leer en celular."""
import markdown
from xhtml2pdf import pisa

INPUT_MD = 'DEFENSA_CELULAR.md'
OUTPUT_PDF = 'DEFENSA_CELULAR.pdf'

with open(INPUT_MD, 'r', encoding='utf-8') as f:
    md_text = f.read()

html_body = markdown.markdown(md_text, extensions=['fenced_code', 'nl2br'])

style = """
@page {
    size: A5;
    margin: 1.2cm 1cm;
}
body {
    font-family: Helvetica, Arial, sans-serif;
    color: #1f2937;
    font-size: 10pt;
    line-height: 1.45;
}
h1 {
    color: #c2410c;
    font-size: 16pt;
    border-bottom: 2pt solid #c2410c;
    padding-bottom: 4pt;
    margin-top: 14pt;
    margin-bottom: 8pt;
}
h2 {
    color: #c2410c;
    font-size: 13pt;
    margin-top: 12pt;
    margin-bottom: 6pt;
}
h3 {
    color: #b45309;
    font-size: 11pt;
    margin-top: 10pt;
    margin-bottom: 5pt;
}
p { margin: 3pt 0; }
strong { color: #c2410c; }
em { color: #6b7280; }
hr { border: 0; border-top: 1pt dashed #d1d5db; margin: 10pt 0; }
ul, ol { margin: 3pt 0 5pt 18pt; padding: 0; }
li { margin-bottom: 2pt; }
code {
    background-color: #fff7ed;
    padding: 1pt 3pt;
    font-family: Courier, monospace;
    font-size: 9pt;
    color: #92400e;
}
pre {
    background-color: #fef3c7;
    border-left: 3pt solid #c2410c;
    padding: 6pt 8pt;
    font-size: 9pt;
}
pre code { background-color: transparent; padding: 0; color: #1f2937; }
blockquote {
    border-left: 3pt solid #fed7aa;
    padding-left: 10pt;
    color: #6b7280;
    font-style: italic;
    margin: 5pt 0;
}
"""

html_full = f"""<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Defensa DevOps - Sabor & Gestion</title>
<style>{style}</style>
</head>
<body>
{html_body}
</body>
</html>"""

with open(OUTPUT_PDF, 'wb') as out_file:
    status = pisa.CreatePDF(html_full, dest=out_file, encoding='utf-8')

if status.err:
    print(f"ERROR generando PDF: {status.err}")
else:
    print(f"PDF generado: {OUTPUT_PDF}")
