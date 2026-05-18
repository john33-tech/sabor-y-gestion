"""Convierte DEFENSA_DEVOPS.md a PDF completo en A4 (referencia exhaustiva)."""
import markdown
from xhtml2pdf import pisa

INPUT_MD = 'DEFENSA_DEVOPS.md'
OUTPUT_PDF = 'DEFENSA_DEVOPS.pdf'

with open(INPUT_MD, 'r', encoding='utf-8') as f:
    md_text = f.read()

html_body = markdown.markdown(md_text, extensions=['fenced_code', 'tables', 'nl2br'])

style = """
@page {
    size: A4;
    margin: 1.8cm 1.5cm;
    @frame footer {
        -pdf-frame-content: footer-content;
        bottom: 0.7cm;
        margin-left: 1.5cm;
        margin-right: 1.5cm;
        height: 0.5cm;
    }
}
body {
    font-family: Helvetica, Arial, sans-serif;
    color: #1f2937;
    font-size: 10.5pt;
    line-height: 1.5;
}
#footer-content {
    text-align: center;
    color: #9ca3af;
    font-size: 8pt;
}
h1 {
    color: #c2410c;
    font-size: 20pt;
    border-bottom: 2pt solid #c2410c;
    padding-bottom: 6pt;
    margin-top: 18pt;
    margin-bottom: 12pt;
    page-break-after: avoid;
}
h2 {
    color: #c2410c;
    font-size: 16pt;
    margin-top: 16pt;
    margin-bottom: 8pt;
    border-bottom: 1pt solid #fed7aa;
    padding-bottom: 3pt;
}
h3 {
    color: #b45309;
    font-size: 13pt;
    margin-top: 12pt;
    margin-bottom: 6pt;
}
h4 {
    color: #92400e;
    font-size: 11.5pt;
    margin-top: 10pt;
    margin-bottom: 4pt;
}
p { margin: 4pt 0; }
strong { color: #c2410c; }
em { color: #6b7280; }
hr { border: 0; border-top: 1pt dashed #d1d5db; margin: 14pt 0; }
ul, ol { margin: 4pt 0 8pt 22pt; padding: 0; }
li { margin-bottom: 3pt; }
code {
    background-color: #fff7ed;
    padding: 1pt 4pt;
    font-family: Courier, monospace;
    font-size: 9.5pt;
    color: #92400e;
}
pre {
    background-color: #fef3c7;
    border-left: 3pt solid #c2410c;
    padding: 8pt 10pt;
    font-size: 9pt;
    margin: 6pt 0;
}
pre code { background-color: transparent; padding: 0; color: #1f2937; }
blockquote {
    border-left: 3pt solid #fed7aa;
    padding-left: 12pt;
    color: #6b7280;
    font-style: italic;
    margin: 6pt 0;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin: 6pt 0;
}
th, td {
    border: 1pt solid #e5e7eb;
    padding: 4pt 8pt;
    text-align: left;
    font-size: 9.5pt;
}
th { background-color: #fff7ed; color: #c2410c; }
"""

html_full = f"""<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Defensa DevOps - Sabor & Gestion - Completo</title>
<style>{style}</style>
</head>
<body>
<div id="footer-content">Sabor &amp; Gestion - Defensa DevOps - Pagina <pdf:pagenumber /> de <pdf:pagecount /></div>
{html_body}
</body>
</html>"""

with open(OUTPUT_PDF, 'wb') as out_file:
    status = pisa.CreatePDF(html_full, dest=out_file, encoding='utf-8')

if status.err:
    print(f"ERROR generando PDF: {status.err}")
else:
    print(f"PDF generado: {OUTPUT_PDF}")
