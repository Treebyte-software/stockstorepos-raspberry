<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="utf-8" />

	<xsl:include href="./report_formats.xsl"/>
	<xsl:include href="./report_text_templates.xsl"/>
	<xsl:include href="./report_xml_templates.xsl"/>

	<xsl:template name="report_heading" match="/table/record">
		<!-- HEADING AND TITLE -->
		<xsl:element name="open_print"></xsl:element>
		<!-- xsl:element name="pulse"></xsl:element -->

		<xsl:call-template name="PrintCenteredHeadingText">
			<xsl:with-param name="value" select="/table/record/COMPANY_DESC" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="40" />
		</xsl:call-template>
		<xsl:call-template name="PrintCenteredHeadingText">
			<xsl:with-param name="value" select="/table/record/COMPANY_STREET" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="40" />
		</xsl:call-template>
		<xsl:call-template name="PrintCenteredHeadingText">
			<xsl:with-param name="value" select="concat(/table/record/COMPANY_ZIP_CODE,' ',/table/record/COMPANY_CITY,' ',/table/record/COMPANY_COUNTRY)" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="40" />
		</xsl:call-template>
		<xsl:call-template name="PrintCenteredHeadingText">
			<xsl:with-param name="value" select="concat('P.IVA ',/table/record/FISCAL_CODE_A)" />
			<xsl:with-param name="font" select="'bold'" />
			<xsl:with-param name="length" select="40" />
		</xsl:call-template>

		<!-- COMPANY_DESC>AG Laboratorio test</COMPANY_DESC>
		<COMPANY_NOTES/>
		<COMPANY_STREET>Via Lungomare Sud</COMPANY_STREET>
		<COMPANY_ZIP_CODE>30015</COMPANY_ZIP_CODE>
		<COMPANY_CITY>Sottomarina di Chioggia</COMPANY_CITY>
		<COMPANY_COUNTRY>VE</COMPANY_COUNTRY -->  

		<!-- xsl:call-template name="PrintHeadingText" -->
		<empty_row />
		<xsl:call-template name="PrintCenteredHeadingText">
			<xsl:with-param name="value" select="'PRECONTO'" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="40" />
		</xsl:call-template>
		<empty_row />
		<xsl:call-template name="PrintDetailText">
			<xsl:with-param name="value" select="concat('CASSA: ', /table/record/POS_CODE)" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="20" />
		</xsl:call-template>
		<xsl:call-template name="PrintDetailText">
			<xsl:with-param name="value" select="concat('NUMERO SCONTRINO: ', format-number(/table/record/TRANSACTION_NUM, '#####0', 'quantity'))" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="20" />
		</xsl:call-template>

		<xsl:if test="string-length(/table/record/TRANSACTION_DATE) = 14">
			<xsl:call-template name="PrintDetailText">
				<xsl:with-param name="value" select="concat('DATA SCONTRINO: ', substring(/table/record/TRANSACTION_DATE, 7, 2), '/', substring(/table/record/TRANSACTION_DATE, 5, 2), ' ', substring(/table/record/TRANSACTION_DATE, 9, 2), ':', substring(/table/record/TRANSACTION_DATE, 11, 2), ':', substring(/table/record/TRANSACTION_DATE, 13, 2))" />
				<xsl:with-param name="font" select="'normal'" />
				<xsl:with-param name="length" select="20" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="string-length(/table/record/TRANSACTION_DATE) = 19">
			<xsl:call-template name="PrintDetailText">
				<xsl:with-param name="value" select="concat('DATA SCONTRINO: ', substring(/table/record/TRANSACTION_DATE, 9, 2), '/', substring(/table/record/TRANSACTION_DATE, 6, 2), ' ', substring(/table/record/TRANSACTION_DATE, 12, 8))" />
				<xsl:with-param name="font" select="'normal'" />
				<xsl:with-param name="length" select="20" />
			</xsl:call-template>
		</xsl:if>

		<xsl:call-template name="PrintDetailText">
			<xsl:with-param name="value" select="concat('OPERATORE: ', /table/record/OPERATOR_DESC)" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="20" />
		</xsl:call-template>

		<xsl:if test="/table/record/OPERATOR_DESC != ''">
			<xsl:call-template name="PrintDetailText">
				<xsl:with-param name="value" select="concat('TAVOLO: ', /table/record/LUNCH_TABLE_DESC)" />
				<xsl:with-param name="font" select="'normal'" />
				<xsl:with-param name="length" select="20" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/table/record/CARD_NUM != ''">
			<xsl:call-template name="PrintDetailText">
				<xsl:with-param name="value" select="concat('NUMERO CARTA: ', /table/record/CARD_NUM)" />
				<xsl:with-param name="font" select="'normal'" />
				<xsl:with-param name="length" select="20" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/table/record/FISCAL_CODE != ''">
			<xsl:call-template name="PrintDetailText">
				<xsl:with-param name="value" select="concat('C.F./P.IVA: ', /table/record/FISCAL_CODE)" />
				<xsl:with-param name="font" select="'normal'" />
				<xsl:with-param name="length" select="20" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/table/record/NOTES != ''">
			<xsl:call-template name="PrintDetailText">
				<xsl:with-param name="value" select="concat('NOTE: ', /table/record/NOTES)" />
				<xsl:with-param name="font" select="'normal'" />
				<xsl:with-param name="length" select="20" />
			</xsl:call-template>
		</xsl:if>

	</xsl:template>

	<xsl:template name="report_footing" match="/table/record">
		<!-- HEADING AND TITLE -->
		<empty_row />
		<!-- xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="concat('T O T A L E   ', format-number(/table/record/TOTAL_AMOUNT, '#####0,00', 'currency'))" />
    <xsl:with-param name="font" select="'double-size'" />
    <xsl:with-param name="length" select="20" />
  </xsl:call-template -->
		<!-- xsl:call-template name="PrintJustifiedText">
			<xsl:with-param name="valueLeft" select="'T O T A L E'" />
			<xsl:with-param name="valueRight" select="format-number(/table/record/TOTAL_AMOUNT, '##0,00', 'currency')" />
			<xsl:with-param name="font" select="'double-size'" />
			<xsl:with-param name="lengthLeft" select="12" />
			<xsl:with-param name="lengthRight" select="8" />
		</xsl:call-template -->
		<xsl:call-template name="PrintJustifiedText">
			<xsl:with-param name="valueLeft" select="concat('T O T A L E     ',/table/record/CURRENCY_DESC)" />
			<xsl:with-param name="valueRight" select="format-number(/table/record/TOTAL_AMOUNT, '##0,00', 'currency')" />
			<xsl:with-param name="font" select="'double-height'" />
			<xsl:with-param name="lengthLeft" select="32" />
			<xsl:with-param name="lengthRight" select="8" />
		</xsl:call-template>
		<empty_row />
		<xsl:call-template name="PrintHeadingText">
			<xsl:with-param name="value" select="'Al netto di promozioni o sconti in cassa'" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="40" />
		</xsl:call-template>

		<empty_row />
		<xsl:call-template name="PrintCenteredHeadingText">
			<xsl:with-param name="value" select="'NON FISCALE'" />
			<xsl:with-param name="font" select="'normal'" />
			<xsl:with-param name="length" select="40" />
		</xsl:call-template>

	</xsl:template>

	<xsl:template match="/">
		<!-- DATA -->
		<xsl:element name="root">

			<xsl:call-template name="report_heading" />

			<empty_row />

			<!-- Cycle for main course -->
			<xsl:for-each select="table/record/child_records/table[@name='Righe fattura (POS)']/record">
			
        <xsl:if test="ROW_TYPE = '3'">
            <!-- voce del menu -->
            <!-- xsl:if test="DESCRIPTION != ''" -->
            <xsl:if test="QUANTITY &gt; 1">
                <xsl:call-template name="PrintJustifiedText">
                        <xsl:with-param name="valueLeft" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')" />
                        <xsl:with-param name="valueRight" select="format-number(PRICE, '##0,00', 'currency')" />
                        <xsl:with-param name="font" select="'normal'" />
                        <xsl:with-param name="lengthLeft" select="12" />
                        <xsl:with-param name="lengthRight" select="8" />
                </xsl:call-template>
            </xsl:if>
            <xsl:call-template name="PrintJustifiedText">
                    <xsl:with-param name="valueLeft" select="substring(DESCRIPTION,1,32)"/>
                    <xsl:with-param name="valueRight" select="format-number(ROW_TOTAL_PRICE, '##0,00', 'currency')" />
                    <xsl:with-param name="font" select="'normal'" />
                    <xsl:with-param name="lengthLeft" select="32" />
                    <xsl:with-param name="lengthRight" select="8" />
            </xsl:call-template>
            <!-- /xsl:if -->
        </xsl:if>


        <xsl:if test="(ROW_TYPE = '2')">
            <!-- Articolo -->
            <xsl:if test="QUANTITY &gt; 1">
                <xsl:call-template name="PrintJustifiedText">
                        <xsl:with-param name="valueLeft" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')" />
                        <xsl:with-param name="valueRight" select="format-number(PRICE, '##0,00', 'currency')" />
                        <xsl:with-param name="font" select="'normal'" />
                        <xsl:with-param name="lengthLeft" select="12" />
                        <xsl:with-param name="lengthRight" select="8" />
                </xsl:call-template>
            </xsl:if>
            <xsl:call-template name="PrintJustifiedText">
                    <xsl:with-param name="valueLeft" select="DESCRIPTION" />
                    <xsl:with-param name="valueRight" select="format-number(ROW_TOTAL_PRICE, '##0,00', 'currency')" />
                    <xsl:with-param name="font" select="'normal'" />
                    <xsl:with-param name="lengthLeft" select="32" />
                    <xsl:with-param name="lengthRight" select="8" />
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="(ROW_TYPE = '4')">
            <!-- Articolo -->
            <xsl:if test="QUANTITY &gt; 1">
                <xsl:call-template name="PrintJustifiedText">
                        <xsl:with-param name="valueLeft" select="concat('  ',format-number(QUANTITY, '##0', 'currency'), ' x ')" />
                        <xsl:with-param name="valueRight" select="' '" />
                        <xsl:with-param name="font" select="'normal'" />
                        <xsl:with-param name="lengthLeft" select="12" />
                        <xsl:with-param name="lengthRight" select="8" />
                </xsl:call-template>
            </xsl:if>
            <xsl:call-template name="PrintJustifiedText">
                    <xsl:with-param name="valueLeft" select="concat('  ',DESCRIPTION)" />
                    <xsl:with-param name="valueRight" select="' '" />
                    <xsl:with-param name="font" select="'normal'" />
                    <xsl:with-param name="lengthLeft" select="39" />
                    <xsl:with-param name="lengthRight" select="1" />
            </xsl:call-template>
        </xsl:if>

        <xsl:if test="(ROW_TYPE = '5')">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintJustifiedText">
                    <xsl:with-param name="valueLeft" select="concat(' + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="valueRight" select="format-number(ROW_TOTAL_PRICE, '##0,00', 'currency')" />
                    <xsl:with-param name="font" select="'normal'"/>
                    <xsl:with-param name="lengthLeft" select="32" />
                    <xsl:with-param name="lengthRight" select="8" />
              </xsl:call-template>
            </xsl:if>
            <xsl:if test="QUANTITY &lt; 0">
              <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(' - ', substring(DESCRIPTION, 1, 37))"/>
                <xsl:with-param name="font" select="'normal'"/>
                <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
        </xsl:if>
        <xsl:if test="(ROW_TYPE = '6')">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintJustifiedText">
                    <xsl:with-param name="valueLeft" select="concat('   + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="valueRight" select="format-number(ROW_TOTAL_PRICE, '##0,00', 'currency')" />
                    <xsl:with-param name="font" select="'normal'"/>
                    <xsl:with-param name="lengthLeft" select="32" />
                    <xsl:with-param name="lengthRight" select="8" />
              </xsl:call-template>
            </xsl:if>
            <xsl:if test="QUANTITY &lt; 0">
              <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat('   - ', substring(DESCRIPTION, 1, 37))"/>
                <xsl:with-param name="font" select="'normal'"/>
                <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
        </xsl:if>

			
			
			
			</xsl:for-each>
			
			
			
			<!-- Totals -->
			<xsl:call-template name="report_footing" />

			<xsl:element name="close_print"></xsl:element>
		</xsl:element>
	</xsl:template>  
</xsl:stylesheet>
