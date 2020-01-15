<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method='text' />

	<xsl:include href="./report_formats.xsl"/>
	<xsl:include href="./report_text_templates.xsl"/>

	<xsl:strip-space elements="*" />

	<xsl:template name="report_heading" match="/">
	   <xsl:for-each select="table/record">
	      <xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="' '" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="CenterString">
			<xsl:with-param name="string" select="COMPANY_DESC" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="CenterString">
			<xsl:with-param name="string" select="concat('P.IVA  ', FISCAL_CODE_B)" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="' '" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="'-'" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="' '" />
			<xsl:with-param name="times" select="1" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>

		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="'Destinatario:'" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="CUSTOMER_DESC" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="CUSTOMER_STREET" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="concat(CUSTOMER_ZIP_CODE, ' ', CUSTOMER_CITY, ' - ', CUSTOMER_COUNTRY)" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="concat('P.IVA  ', CUSTOMER_FISCAL_CODE_A)" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>

		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="' '" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="'-'" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="concat('FATTURA N. ',INVOICE_NUM,' DEL ', substring(ACCOUNTING_DATE, 7, 2), '/', substring(ACCOUNTING_DATE, 5, 2), '/', substring(ACCOUNTING_DATE, 1, 4))" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="'-'" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>

		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="' '" />
			<xsl:with-param name="times" select="1" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="'ARTICOLO'" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="'UM  QTA        PREZZO          IVA      '" />
			<xsl:with-param name="length" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="'-'" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="/">
		<!-- DATA -->
		<xsl:call-template name="report_heading" />
		<xsl:for-each select="table/record/child_records/table[@name = 'Righe fattura (POS)']/record">
			<xsl:call-template name="LeftString">
				<xsl:with-param name="string" select="substring(DESCRIPTION, 1, 39)" />
				<xsl:with-param name="length" select="40" />
				<xsl:with-param name="addcrlf" select="1" />
			</xsl:call-template>
			<xsl:call-template name="RightString">
				<xsl:with-param name="string" select="concat(substring(MEAS_UNIT_CODE, 1, 2), ' ')" />
				<xsl:with-param name="length" select="3" />
				<xsl:with-param name="addcrlf" select="0" />
			</xsl:call-template>
			<xsl:call-template name="RightString">
				<xsl:with-param name="string" select="concat(substring(format-number(QUANTITY,'##0,00', 'quantity'), 1, 9), ' ')" />
				<xsl:with-param name="length" select="10" />
				<xsl:with-param name="addcrlf" select="0" />
			</xsl:call-template>
			<xsl:call-template name="RightString">
				<xsl:with-param name="string" select="concat(substring(format-number(ROW_TOTAL_PRICE,'##0,00', 'currency'), 1, 14), ' ')" />
				<xsl:with-param name="length" select="15" />
				<xsl:with-param name="addcrlf" select="0" />
			</xsl:call-template>
			<xsl:call-template name="RightString">
				<xsl:with-param name="string" select="substring(VAT_DESC, 1, 12)" />
				<xsl:with-param name="length" select="12" />
				<xsl:with-param name="addcrlf" select="1" />
			</xsl:call-template>
		</xsl:for-each>	
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="' '" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="'-'" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>

		<xsl:for-each select="table/record/child_records/table[@name = 'Totali fattura (POS)']/record">
			<xsl:call-template name="LeftString">
				<xsl:with-param name="string" select="'DESCR. ALIQUOTA     IMPONIBILE   IMPOSTA'" />
				<xsl:with-param name="length" select="40" />
				<xsl:with-param name="addcrlf" select="1" />
			</xsl:call-template>

			<xsl:call-template name="RightString">
				<xsl:with-param name="string" select="concat(substring(VAT_DESC, 1, 19), ' ')" />
				<xsl:with-param name="length" select="20" />
				<xsl:with-param name="addcrlf" select="0" />
			</xsl:call-template>
			<xsl:call-template name="RightString">
				<xsl:with-param name="string" select="concat(substring(format-number(NET_AMOUNT,'##0,00', 'currency'), 1, 12), ' ')" />
				<xsl:with-param name="length" select="13" />
				<xsl:with-param name="addcrlf" select="0" />
			</xsl:call-template>
			<xsl:call-template name="RightString">
				<xsl:with-param name="string" select="substring(format-number(VAT_AMOUNT,'##0,00', 'currency'), 1, 7)" />
				<xsl:with-param name="length" select="7" />
				<xsl:with-param name="addcrlf" select="1" />
			</xsl:call-template>
		</xsl:for-each>	

		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="'-'" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
		<xsl:call-template name="RepeatString">
			<xsl:with-param name="string" select="' '" />
			<xsl:with-param name="times" select="40" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>

		<xsl:call-template name="LeftString">
			<xsl:with-param name="string" select="'T O T A L E'" />
			<xsl:with-param name="length" select="20" />
			<xsl:with-param name="addcrlf" select="0" />
		</xsl:call-template>
		<xsl:call-template name="RightString">
			<xsl:with-param name="string" select="format-number(/table/record/TOTAL_AMOUNT,'###.##0,00','currency')" />
			<xsl:with-param name="length" select="20" />
			<xsl:with-param name="addcrlf" select="1" />
		</xsl:call-template>
	</xsl:template>
</xsl:stylesheet>