<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:output method="xml" version="1.0" encoding="utf-8" />

<xsl:template match="/">

<xsl:element name="table">
  <xsl:attribute name="name">SST_SALE_HEADINGS</xsl:attribute>
  <xsl:attribute name="parameters"></xsl:attribute>
  <xsl:element name="recordcount">1</xsl:element>
  <!-- Heading of transaction -->
  <xsl:for-each select="/BOM/BO/ODLN/row">
    <xsl:element name="record">
      <xsl:attribute name="ID"><xsl:value-of select="DocEntry" /></xsl:attribute>
      <xsl:attribute name="SHOP_ID"></xsl:attribute>
      <xsl:attribute name="POS_ID"></xsl:attribute>
      <xsl:attribute name="SALE_TYPE_ID">1</xsl:attribute>
      <xsl:attribute name="TRANSACTION_NUM"><xsl:value-of select="DocNum" /></xsl:attribute>
      <xsl:attribute name="TRANSACTION_DATE"><xsl:value-of select="concat(DocDate,'000000')" /></xsl:attribute>
      <xsl:attribute name="ACCOUNTING_DATE"><xsl:value-of select="concat(DocDate,'000000')" /></xsl:attribute>
      <xsl:attribute name="CARD_NUM"><xsl:value-of select="CardCode" /></xsl:attribute>
      <xsl:attribute name="REFERENCE_CARD_NUM"><xsl:value-of select="U_Veterinario" /></xsl:attribute>
      <xsl:attribute name="SALE_PRICE_LIST_ID"></xsl:attribute>
      <xsl:attribute name="CURRENCY_ID">EUR</xsl:attribute>
      <xsl:attribute name="TOTAL_AMOUNT"><xsl:value-of select="DocTotal" /></xsl:attribute>
      <xsl:attribute name="SUSPENDED">1</xsl:attribute>
      <xsl:attribute name="LINK_EXTERNAL_DOC_1"><xsl:value-of select="ObjType" /></xsl:attribute>
      <xsl:attribute name="LINK_EXTERNAL_DOC_2"><xsl:value-of select="DocEntry" /></xsl:attribute>
      <xsl:element name="child_records">
        <!-- Details (articles) of transaction -->
        <xsl:element name="table">
          <xsl:attribute name="name">SST_SALE_DETAILS</xsl:attribute>
          <xsl:attribute name="parameters"></xsl:attribute>
          <xsl:for-each select="/BOM/BO/DLN1/row">
            <xsl:variable name="current_doc_type" select="ObjType" />
            <xsl:variable name="current_doc_entry" select="DocEntry" />
            <xsl:variable name="current_line_num" select="LineNum" />
            <xsl:element name="record">
              <xsl:attribute name="ID">0</xsl:attribute>
              <xsl:attribute name="HEADING_ID"><xsl:value-of select="DocEntry" /></xsl:attribute>
              <xsl:attribute name="DETAIL_ORDER"><xsl:value-of select="(LineNum + 1) * 20" /></xsl:attribute>
              <xsl:attribute name="SALE_ARTICLE_ID"><xsl:value-of select="ItemCode" /></xsl:attribute>
              <xsl:attribute name="CARD_NUM"><xsl:value-of select="BaseCard" /></xsl:attribute>
              <xsl:attribute name="MEAS_UNIT_ID"><xsl:value-of select="unitMsr" /></xsl:attribute>
              <xsl:attribute name="CONVERSION_FACTOR">1</xsl:attribute>
              <xsl:attribute name="QUANTITY"><xsl:value-of select="Quantity" /></xsl:attribute>
              <xsl:attribute name="VAT_ID"><xsl:value-of select="VatGroup" /></xsl:attribute>
              <xsl:attribute name="VAT_PERCENT"><xsl:value-of select="VatPrcnt" /></xsl:attribute>
              <xsl:attribute name="PRICE"><xsl:value-of select="format-number((U_BaseLinePrice * (1 - U_BaseLineDisc div 100)) * (1 + VatPrcnt div 100), '####.##')" /></xsl:attribute>
              <xsl:attribute name="REFERENCE_PRICE"><xsl:value-of select="U_BaseLinePrice" /></xsl:attribute>
              <xsl:attribute name="DISCOUNT"><xsl:value-of select="format-number((U_BaseLinePrice * (1 - U_BaseLineDisc div 100)) * (1 + VatPrcnt div 100) * (DiscPrcnt div 100) * Quantity * -1.0, '####.##')" /></xsl:attribute>
              <xsl:attribute name="PROMOTION_DISCOUNT">0</xsl:attribute>
              <xsl:attribute name="ROW_TOTAL_PRICE"><xsl:value-of select="format-number((U_BaseLinePrice * (1 - U_BaseLineDisc div 100)) * (1 + VatPrcnt div 100) * Quantity, '####.##')" /></xsl:attribute>
              <xsl:attribute name="LOT_EXTERNAL_CODE"><xsl:value-of select="/BOM/BO/BTNT/row[DocType = $current_doc_type and DocEntry = $current_doc_entry and DocLineNum = $current_line_num]/DistNumber" /></xsl:attribute>
              <xsl:attribute name="EXPIRATION_DATE"><xsl:value-of select="/BOM/BO/BTNT/row[DocType = $current_doc_type and DocEntry = $current_doc_entry and DocLineNum = $current_line_num]/ExpDate" /></xsl:attribute>
              <xsl:attribute name="LINK_EXTERNAL_DOC_1"><xsl:value-of select="ObjType" /></xsl:attribute>
              <xsl:attribute name="LINK_EXTERNAL_DOC_2"><xsl:value-of select="DocEntry" /></xsl:attribute>
              <xsl:attribute name="LINK_EXTERNAL_DOC_3"><xsl:value-of select="LineNum" /></xsl:attribute>
              <xsl:value-of select="$current_line_num" />
            </xsl:element>
          </xsl:for-each>
        </xsl:element>
        <!-- Details (discounts) of transaction -->
<!--        
        <xsl:element name="table">
          <xsl:attribute name="name">SST_SALE_DETAILS</xsl:attribute>
          <xsl:attribute name="parameters"></xsl:attribute>
          <xsl:for-each select="/BOM/BO/DLN1/row[DiscPrcnt != 0]">
            <xsl:variable name="current_doc_type" select="ObjType" />
            <xsl:variable name="current_doc_entry" select="DocEntry" />
            <xsl:variable name="current_line_num" select="LineNum" />
            <xsl:element name="record">
              <xsl:attribute name="ID">0</xsl:attribute>
              <xsl:attribute name="HEADING_ID"><xsl:value-of select="DocEntry" /></xsl:attribute>
              <xsl:attribute name="DETAIL_ORDER"><xsl:value-of select="((LineNum + 1) * 20) + 1" /></xsl:attribute>
              <xsl:attribute name="SALE_DISCOUNT_ID"><xsl:value-of select="151" /></xsl:attribute>
              <xsl:attribute name="QUANTITY"><xsl:value-of select="DiscPrcnt" /></xsl:attribute>
              <xsl:attribute name="AMOUNT"><xsl:value-of select="format-number((U_BaseLinePrice * (1 - U_BaseLineDisc div 100)) * (1 + VatPrcnt div 100) * (DiscPrcnt div 100) * Quantity * -1.0, '####.##')" /></xsl:attribute>
              <xsl:attribute name="LINK_EXTERNAL_DOC_1"><xsl:value-of select="ObjType" /></xsl:attribute>
              <xsl:attribute name="LINK_EXTERNAL_DOC_2"><xsl:value-of select="DocEntry" /></xsl:attribute>
              <xsl:attribute name="LINK_EXTERNAL_DOC_3"><xsl:value-of select="LineNum" /></xsl:attribute>
              <xsl:value-of select="$current_line_num" />
            </xsl:element>
          </xsl:for-each>
        </xsl:element>
-->
      </xsl:element>
    </xsl:element>
  </xsl:for-each>
</xsl:element>  
</xsl:template>
</xsl:stylesheet>