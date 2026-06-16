import datetime as dt
import html
import os
import sqlite3
import zipfile
from pathlib import Path
from xml.etree.ElementTree import Element, SubElement, tostring


BASE_DIR = Path(__file__).resolve().parent
DB_PATH = BASE_DIR / "base.db"
OUT_PATH = BASE_DIR / "base.xlsx"
TABLES = ["Courses", "Students", "Enrollments"]


def col_name(index):
    name = ""
    while index:
        index, remainder = divmod(index - 1, 26)
        name = chr(65 + remainder) + name
    return name


def xml_decl(body):
    return b'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>\n' + body


def attrs(**kwargs):
    return {k.replace("_", ":"): str(v) for k, v in kwargs.items() if v is not None}


def fetch_table(conn, table):
    cur = conn.execute(f"SELECT * FROM {table}")
    headers = [item[0] for item in cur.description]
    return headers, cur.fetchall()


def build_shared_strings(sheets):
    strings = []
    index = {}
    for headers, rows in sheets.values():
        for value in list(headers) + [cell for row in rows for cell in row]:
            if value is None or isinstance(value, (int, float)):
                continue
            text = str(value)
            if text not in index:
                index[text] = len(strings)
                strings.append(text)
    root = Element(
        "sst",
        xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main",
        count=str(len(strings)),
        uniqueCount=str(len(strings)),
    )
    for text in strings:
        si = SubElement(root, "si")
        t = SubElement(si, "t")
        t.text = text
    return index, xml_decl(tostring(root, encoding="utf-8"))


def worksheet_xml(headers, rows, shared):
    root = Element("worksheet", xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main")
    SubElement(root, "dimension", ref=f"A1:{col_name(len(headers))}{len(rows) + 1}")
    views = SubElement(root, "sheetViews")
    view = SubElement(views, "sheetView", workbookViewId="0")
    SubElement(view, "pane", ySplit="1", topLeftCell="A2", activePane="bottomLeft", state="frozen")

    cols = SubElement(root, "cols")
    for i, header in enumerate(headers, 1):
        width = max(12, min(24, len(str(header)) + 6))
        SubElement(cols, "col", min=str(i), max=str(i), width=str(width), customWidth="1")

    data = SubElement(root, "sheetData")
    write_row(data, 1, headers, shared, header=True)
    for row_index, row in enumerate(rows, 2):
        write_row(data, row_index, row, shared)

    SubElement(root, "autoFilter", ref=f"A1:{col_name(len(headers))}{len(rows) + 1}")
    return xml_decl(tostring(root, encoding="utf-8"))


def write_row(parent, row_index, values, shared, header=False):
    row_el = SubElement(parent, "row", r=str(row_index))
    for col_index, value in enumerate(values, 1):
        cell_ref = f"{col_name(col_index)}{row_index}"
        style = "1" if header else None
        if value is None:
            SubElement(row_el, "c", attrs(r=cell_ref, s=style))
        elif isinstance(value, (int, float)):
            cell = SubElement(row_el, "c", attrs(r=cell_ref, s=style))
            SubElement(cell, "v").text = str(value)
        else:
            cell = SubElement(row_el, "c", attrs(r=cell_ref, t="s", s=style))
            SubElement(cell, "v").text = str(shared[str(value)])


def workbook_xml(sheet_names):
    root = Element("workbook", xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main", **{
        "xmlns:r": "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
    })
    sheets_el = SubElement(root, "sheets")
    for i, name in enumerate(sheet_names, 1):
        SubElement(sheets_el, "sheet", name=name, sheetId=str(i), **{"r:id": f"rId{i}"})
    return xml_decl(tostring(root, encoding="utf-8"))


def workbook_rels(sheet_names):
    root = Element("Relationships", xmlns="http://schemas.openxmlformats.org/package/2006/relationships")
    for i, _ in enumerate(sheet_names, 1):
        SubElement(root, "Relationship", Id=f"rId{i}", Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet", Target=f"worksheets/sheet{i}.xml")
    SubElement(root, "Relationship", Id=f"rId{len(sheet_names)+1}", Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles", Target="styles.xml")
    SubElement(root, "Relationship", Id=f"rId{len(sheet_names)+2}", Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings", Target="sharedStrings.xml")
    return xml_decl(tostring(root, encoding="utf-8"))


def content_types(sheet_count):
    root = Element("Types", xmlns="http://schemas.openxmlformats.org/package/2006/content-types")
    SubElement(root, "Default", Extension="rels", ContentType="application/vnd.openxmlformats-package.relationships+xml")
    SubElement(root, "Default", Extension="xml", ContentType="application/xml")
    SubElement(root, "Override", PartName="/xl/workbook.xml", ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml")
    SubElement(root, "Override", PartName="/xl/styles.xml", ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml")
    SubElement(root, "Override", PartName="/xl/sharedStrings.xml", ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml")
    SubElement(root, "Override", PartName="/docProps/core.xml", ContentType="application/vnd.openxmlformats-package.core-properties+xml")
    SubElement(root, "Override", PartName="/docProps/app.xml", ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml")
    for i in range(1, sheet_count + 1):
        SubElement(root, "Override", PartName=f"/xl/worksheets/sheet{i}.xml", ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml")
    return xml_decl(tostring(root, encoding="utf-8"))


def root_rels():
    root = Element("Relationships", xmlns="http://schemas.openxmlformats.org/package/2006/relationships")
    SubElement(root, "Relationship", Id="rId1", Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument", Target="xl/workbook.xml")
    SubElement(root, "Relationship", Id="rId2", Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties", Target="docProps/core.xml")
    SubElement(root, "Relationship", Id="rId3", Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties", Target="docProps/app.xml")
    return xml_decl(tostring(root, encoding="utf-8"))


def styles_xml():
    return xml_decl(b"""<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>
<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill></fills>
<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>
<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>""")


def doc_props():
    now = dt.datetime.now(dt.UTC).replace(microsecond=0).isoformat().replace("+00:00", "Z")
    core = f"""<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator>Codex</dc:creator><cp:lastModifiedBy>Codex</cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">{html.escape(now)}</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">{html.escape(now)}</dcterms:modified></cp:coreProperties>"""
    app = """<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Codex</Application></Properties>"""
    return xml_decl(core.encode("utf-8")), xml_decl(app.encode("utf-8"))


def main():
    conn = sqlite3.connect(DB_PATH)
    sheets = {table: fetch_table(conn, table) for table in TABLES}
    shared, shared_xml = build_shared_strings(sheets)
    core_xml, app_xml = doc_props()

    if OUT_PATH.exists():
        OUT_PATH.unlink()
    with zipfile.ZipFile(OUT_PATH, "w", zipfile.ZIP_DEFLATED) as xlsx:
        xlsx.writestr("[Content_Types].xml", content_types(len(TABLES)))
        xlsx.writestr("_rels/.rels", root_rels())
        xlsx.writestr("docProps/core.xml", core_xml)
        xlsx.writestr("docProps/app.xml", app_xml)
        xlsx.writestr("xl/workbook.xml", workbook_xml(TABLES))
        xlsx.writestr("xl/_rels/workbook.xml.rels", workbook_rels(TABLES))
        xlsx.writestr("xl/styles.xml", styles_xml())
        xlsx.writestr("xl/sharedStrings.xml", shared_xml)
        for i, table in enumerate(TABLES, 1):
            headers, rows = sheets[table]
            xlsx.writestr(f"xl/worksheets/sheet{i}.xml", worksheet_xml(headers, rows, shared))

    print(f"created {OUT_PATH}")


if __name__ == "__main__":
    main()
