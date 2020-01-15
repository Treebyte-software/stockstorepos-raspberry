<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" version="1.0" encoding="utf-8"/>

<xsl:include href="./report_formats.xsl"/>
<xsl:include href="./report_text_templates.xsl"/>
<xsl:include href="./report_xml_templates.xsl"/>

<xsl:template name="report_heading" match="/table/record">
<!-- HEADING AND TITLE -->
  <xsl:element name="open_print"/>
  <empty_row/>
  <empty_row/>
  <empty_row/>
  <empty_row/>
  <empty_row/>
  <xsl:if test="((/table/record/SALE_TYPE_CODE != '10') and (/table/record/SALE_TYPE_CODE != '103'))">
    <!-- xsl:if test="/table/record/TEXT_FIELD_4 != ''">    
      <xsl:call-template name="PrintHeadingText">
        <xsl:with-param name="value" select="concat('Tavolo: ', /table/record/TEXT_FIELD_4)"/>
        <xsl:with-param name="font" select="'double-size'"/>
        <xsl:with-param name="length" select="20"/>
      </xsl:call-template>
    </xsl:if -->
    <!-- xsl:if test="/table/record/TEXT_FIELD_4 = ''" -->    
      <xsl:call-template name="PrintHeadingText">
        <xsl:with-param name="value" select="concat('Tavolo: ', /table/record/LUNCH_TABLE_DESC)"/>
        <xsl:with-param name="font" select="'double-size'"/>
        <xsl:with-param name="length" select="20"/>
      </xsl:call-template>
    <!-- /xsl:if -->
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="concat('Num. ospiti: ', /table/record/REAL_GUESTS)"/>
      <xsl:with-param name="font" select="'normal'"/>
      <xsl:with-param name="length" select="40"/>
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="((/table/record/SALE_TYPE_CODE = '10') or (/table/record/SALE_TYPE_CODE = '103'))">
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="/table/record/SALE_TYPE_DESC"/>
      <xsl:with-param name="font" select="'double-size'"/>
      <xsl:with-param name="length" select="20"/>
    </xsl:call-template>
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="concat('Consegna: ', /table/record/TEXT_FIELD_3)"/>
      <xsl:with-param name="font" select="'double-size'"/>
      <xsl:with-param name="length" select="20"/>
    </xsl:call-template>
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="concat('CLIENTE: ', /table/record/CUSTOMER)"/>
      <xsl:with-param name="font" select="'double-height'"/>
      <xsl:with-param name="length" select="40"/>
    </xsl:call-template>
  </xsl:if>
  <xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="concat('Numero: ', format-number(/table/record/TRANSACTION_NUM, '#####0', 'quantity'))"/>
    <xsl:with-param name="font" select="'normal'"/>
    <xsl:with-param name="length" select="40"/>
  </xsl:call-template>
  <xsl:if test="string-length(/table/record/PRINT_DATE) = 14">
    <!-- source format: YYYYMMDDHHNNSS, length 14, from IAEngine -->
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="concat('Stampato: ', substring(/table/record/PRINT_DATE, 7, 2), '/', substring(/table/record/PRINT_DATE, 5, 2), ' ', substring(/table/record/PRINT_DATE, 9, 2), ':', substring(/table/record/PRINT_DATE, 11, 2), ':', substring(/table/record/PRINT_DATE, 13, 2))"/>
      <xsl:with-param name="font" select="'normal'"/>
      <xsl:with-param name="length" select="40"/>
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="string-length(/table/record/PRINT_DATE) = 19">
    <!-- source format: YYYY-MM-DD HH:NN:SS, length 19, from PHP -->
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="concat('Stampato: ', substring(/table/record/PRINT_DATE, 9, 2), '/', substring(/table/record/PRINT_DATE, 6, 2), ' ', substring(/table/record/PRINT_DATE, 12, 8))"/>
      <xsl:with-param name="font" select="'normal'"/>
      <xsl:with-param name="length" select="40"/>
    </xsl:call-template>
  </xsl:if>
  <xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="concat('Operatore: ', /table/record/OPERATOR_DESC)"/>
    <xsl:with-param name="font" select="'normal'"/>
    <xsl:with-param name="length" select="40"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="/">
<!-- DATA -->
  <xsl:element name="root">
  <!-- empty_row/>
  <empty_row/>
  <empty_row/>
  <empty_row/>
  <empty_row/ -->
  <xsl:call-template name="report_heading"/>

  <empty_row/>
  <xsl:call-template name="PrintHeadingText">
    <!-- xsl:with-param name="value" select="/table/record/child_records/table[@name='Ordinazione articoli per portata']/record[1]/PRODUCTION_CENTER_DESC" / -->
    <xsl:with-param name="value" select="/table/record/child_records/table[@name='Ordinazione articoli per portata']/record[1]/REQUEST_PROD_CENTER_DESC"/>
    <xsl:with-param name="font" select="'double-height'"/>
    <xsl:with-param name="length" select="40"/>
  </xsl:call-template>

  <!-- Cycle for main course -->
  <xsl:for-each select="table/record/child_records/table[@name='Ordinazione articoli per portata']/record">

    <xsl:if test="(not(preceding-sibling::record[1]) or (COURSE_DESC != preceding-sibling::record[1]/COURSE_DESC))">
      <xsl:if test="COURSE_DESC != ''">
        <!-- xsl:call-template name="PrintHeadingText">
            <xsl:with-param name="value" select="substring(COURSE_DESC,1,20)"/>
            <xsl:with-param name="font" select="'double-size'"/>
            <xsl:with-param name="length" select="20"/>
        </xsl:call-template -->
        <xsl:call-template name="PrintHeadingText">
            <xsl:with-param name="value" select="substring(COURSE_DESC,1,40)"/>
            <xsl:with-param name="font" select="'bold'"/>
            <xsl:with-param name="length" select="40"/>
        </xsl:call-template>
      </xsl:if>
    </xsl:if>
    
    <!-- Normal rows -->
    <xsl:if test="DELETED = 0">
    
    
<!-- ROW_TYPE
    0   coperto
    1   portata
    2   articolo
    3   menu testa
    4   riga menu
    5   riga variante
    6   riga variante menu
    7   riga nota
    8   riga nota variante
    9   riga nota variante menu
    10  riga note menu
-->    
    
        <!-- xsl:if test="ROW_TYPE != '3'">
          <xsl:if test="(not(preceding-sibling::record[1]) or (MENU_DESC != preceding-sibling::record[1]/MENU_DESC))" -->
          <xsl:if test="(not(preceding-sibling::record[1]) or (MASTER_MENU != preceding-sibling::record[1]/MASTER_MENU))">
        <!-- xsl:if test="ROW_TYPE = '3'" -->
            <!-- voce del menu -->
            <xsl:if test="MASTER_MENU != ''">
              <xsl:if test="string-length(MASTER_MENU) &gt; 30">
                  <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('       (',substring(MASTER_MENU,1,30),')' )"/>
                    <xsl:with-param name="font" select="'normal'"/>
                    <xsl:with-param name="length" select="40"/>
                  </xsl:call-template>
              </xsl:if>
              <xsl:if test="string-length(MASTER_MENU) &lt; 31">
                  <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat(substring('                                      ',1,38 - string-length(MASTER_MENU)), '(',MASTER_MENU,')' )"/>
                    <xsl:with-param name="font" select="'normal'"/>
                    <xsl:with-param name="length" select="40"/>
                  </xsl:call-template>
              </xsl:if>
            </xsl:if>
        <!-- /xsl:if -->
          </xsl:if>
        <!-- /xsl:if -->

        <xsl:if test="((ROW_TYPE = '2') or (ROW_TYPE = '4'))">
            <!-- Articolo -->
            <!-- xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')"/>
                <xsl:with-param name="font" select="'double-size'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(DESCRIPTION, 1, 40)"/>
                <xsl:with-param name="font" select="'double-width'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template -->
            <!-- xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(concat(format-number(QUANTITY, '#0', 'currency'), '  ', DESCRIPTION), 1, 40)"/>
                <xsl:with-param name="font" select="'double-height'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(concat(format-number(QUANTITY, '#0', 'currency'), '  ', DESCRIPTION), 1, 20)"/>
                <xsl:with-param name="font" select="'double-size'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
        </xsl:if>

        <xsl:if test="((ROW_TYPE = '5') or (ROW_TYPE = '6'))">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat(' + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="font" select="'double-height'"/>
                    <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
            <xsl:if test="QUANTITY &lt; 0">
              <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(' - ', substring(DESCRIPTION, 1, 37))"/>
                <xsl:with-param name="font" select="'double-height'"/>
                <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
        </xsl:if>

        <xsl:if test="ARTICLE_NOTES != ''"> <!-- libere -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                <xsl:with-param name="font" select="'underline-double-height'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
                <!-- xsl:with-param name="font" select="'underline'"/ -->

        <xsl:if test="((ROW_TYPE = '7') or (ROW_TYPE = '8') or (ROW_TYPE = '9') or (ROW_TYPE = '10'))">
            <!-- libere -->
            <!-- xsl:if test="ARTICLE_NOTES != ''">
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                    <xsl:with-param name="font" select="'underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if -->
            <xsl:if test="RECORD_TYPE = 3"> <!-- codificate -->
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(DESCRIPTION, 1, 38))"/>
                    <xsl:with-param name="font" select="'underline-double-height'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if>
        </xsl:if>
    
    </xsl:if>

    <!-- Annulled rows -->
    <xsl:if test="DELETED = 1">

        <!--xsl:if test="ROW_TYPE != '3'">
            <xsl:if test="(not(preceding-sibling::record[1]) or (MENU_DESC != preceding-sibling::record[1]/MENU_DESC))">
        <xsl:if test="ROW_TYPE = '3'" -->
            <xsl:if test="(not(preceding-sibling::record[1]) or (MASTER_MENU != preceding-sibling::record[1]/MASTER_MENU))">
                <xsl:if test="MASTER_MENU != ''">
                    <xsl:if test="string-length(MASTER_MENU) &gt; 30">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat('       (',substring(MASTER_MENU,1,30),')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                    <xsl:if test="string-length(MASTER_MENU) &lt; 31">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat(substring('                                      ',1,38 - string-length(MASTER_MENU)), '(',MASTER_MENU,')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                </xsl:if>
        </xsl:if>
            <!-- /xsl:if>
        </xsl:if -->

        <xsl:if test="((ROW_TYPE = '2') or (ROW_TYPE = '4'))">
            <!-- Articolo -->
            <!-- xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')"/>
                <xsl:with-param name="font" select="'reverse-double-size'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(DESCRIPTION, 1, 40)"/>
                <xsl:with-param name="font" select="'reverse-double-width'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(concat(format-number(QUANTITY, '#0', 'currency'), '  ', DESCRIPTION), 1, 40)"/>
                <xsl:with-param name="font" select="'reverse-double-height'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>

        <xsl:if test="((ROW_TYPE = '5') or (ROW_TYPE = '6'))">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat(' + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="font" select="'reverse-normal'"/>
                    <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
            <xsl:if test="QUANTITY &lt; 0">
              <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(' - ', substring(DESCRIPTION, 1, 37))"/>
                <xsl:with-param name="font" select="'reverse-normal'"/>
                <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
        </xsl:if>

        <xsl:if test="ARTICLE_NOTES != ''"> <!-- libere -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                <xsl:with-param name="font" select="'reverse-underline'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="((ROW_TYPE = '7') or (ROW_TYPE = '8') or (ROW_TYPE = '9') or (ROW_TYPE = '10'))">
            <!-- xsl:if test="ARTICLE_NOTES != ''">
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                    <xsl:with-param name="font" select="'reverse-underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if -->
            <xsl:if test="RECORD_TYPE = 3"> <!-- codificate -->
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(DESCRIPTION, 1, 38))"/>
                    <xsl:with-param name="font" select="'reverse-underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if>
        </xsl:if>
                
    </xsl:if>
  </xsl:for-each>

  <!-- Cycle for other production centers -->
  <xsl:if test="table/record/child_records/table[@name='Ordinazione articoli per altri CDP']/record != ''">
    <empty_row/>
    <xsl:for-each select="table/record/child_records/table[@name='Ordinazione articoli per altri CDP']/record">

      <xsl:if test="DELETED=0">    
        <!-- xsl:if test="(not(preceding-sibling::record[1]) or (REQUEST_PROD_CENTER_DESC != preceding-sibling::record[1]/REQUEST_PROD_CENTER_DESC))" -->
        <xsl:if test="(not(preceding-sibling::record[1]) or (PRODUCTION_CENTER_DESC != preceding-sibling::record[1]/PRODUCTION_CENTER_DESC))">
            <empty_row/>
                <!-- xsl:with-param name="value" select="REQUEST_PROD_CENTER_DESC" / -->
            <xsl:call-template name="PrintHeadingText">
                <xsl:with-param name="value" select="PRODUCTION_CENTER_DESC"/>
                <xsl:with-param name="font" select="'bold'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
        </xsl:if>
        
        <!-- xsl:if test="ROW_TYPE != '3'">
            <xsl:if test="(not(preceding-sibling::record[1]) or (MENU_DESC != preceding-sibling::record[1]/MENU_DESC))">
        <xsl:if test="ROW_TYPE = '3'" -->
            <xsl:if test="(not(preceding-sibling::record[1]) or (MASTER_MENU != preceding-sibling::record[1]/MASTER_MENU))">
                <xsl:if test="MASTER_MENU != ''">
                    <xsl:if test="string-length(MASTER_MENU) &gt; 30">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat('       (',substring(MASTER_MENU,1,30),')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                    <xsl:if test="string-length(MASTER_MENU) &lt; 31">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat(substring('                                      ',1,38 - string-length(MASTER_MENU)), '(',MASTER_MENU,')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                </xsl:if>
        </xsl:if>
            <!-- /xsl:if>
        </xsl:if -->
        
        <xsl:if test="((ROW_TYPE = '2') or (ROW_TYPE = '4'))">
            <!-- Articolo -->
            <!-- xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')"/>
                <xsl:with-param name="font" select="'normal'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(DESCRIPTION, 1, 40)"/>
                <xsl:with-param name="font" select="'normal'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(concat(format-number(QUANTITY, '#0', 'currency'), '  ', DESCRIPTION), 1, 40)"/>
                <xsl:with-param name="font" select="'normal'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>

        <xsl:if test="((ROW_TYPE = '5') or (ROW_TYPE = '6'))">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat(' + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="font" select="'normal'"/>
                    <xsl:with-param name="length" select="40"/>
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

        <xsl:if test="ARTICLE_NOTES != ''"> <!-- libere -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                <xsl:with-param name="font" select="'underline'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="((ROW_TYPE = '7') or (ROW_TYPE = '8') or (ROW_TYPE = '9') or (ROW_TYPE = '10'))">
            <!-- libere -->
            <!-- xsl:if test="ARTICLE_NOTES != ''">
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                    <xsl:with-param name="font" select="'underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if -->
            <xsl:if test="RECORD_TYPE = 3"> <!-- codificate -->
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(DESCRIPTION, 1, 38))"/>
                    <xsl:with-param name="font" select="'underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if>
        </xsl:if>
      </xsl:if>
      
      <xsl:if test="DELETED=1">    
        <!-- xsl:if test="(not(preceding-sibling::record[1]) or (REQUEST_PROD_CENTER_DESC != preceding-sibling::record[1]/REQUEST_PROD_CENTER_DESC))" -->
        <xsl:if test="(not(preceding-sibling::record[1]) or (PRODUCTION_CENTER_DESC != preceding-sibling::record[1]/PRODUCTION_CENTER_DESC))">
            <empty_row/>
                <!-- xsl:with-param name="value" select="REQUEST_PROD_CENTER_DESC" / -->
            <xsl:call-template name="PrintHeadingText">
                <xsl:with-param name="value" select="PRODUCTION_CENTER_DESC"/>
                <xsl:with-param name="font" select="'bold'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
        </xsl:if>
        
        <!-- xsl:if test="ROW_TYPE != '3'">
            <xsl:if test="(not(preceding-sibling::record[1]) or (MENU_DESC != preceding-sibling::record[1]/MENU_DESC))">
        <xsl:if test="ROW_TYPE = '3'" -->
            <xsl:if test="(not(preceding-sibling::record[1]) or (MASTER_MENU != preceding-sibling::record[1]/MASTER_MENU))">
                <xsl:if test="MASTER_MENU != ''">
                    <xsl:if test="string-length(MASTER_MENU) &gt; 30">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat('       (',substring(MASTER_MENU,1,30),')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                    <xsl:if test="string-length(MASTER_MENU) &lt; 31">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat(substring('                                      ',1,38 - string-length(MASTER_MENU)), '(',MASTER_MENU,')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                </xsl:if>
        </xsl:if>
            <!-- /xsl:if>
        </xsl:if -->
        
        <xsl:if test="((ROW_TYPE = '2') or (ROW_TYPE = '4'))">
            <!-- Articolo -->
            <!-- xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')"/>
                <xsl:with-param name="font" select="'reverse-normal'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(DESCRIPTION, 1, 40)"/>
                <xsl:with-param name="font" select="'reverse-normal'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(concat(format-number(QUANTITY, '#0', 'currency'), '  ', DESCRIPTION), 1, 40)"/>
                <xsl:with-param name="font" select="'reverse-normal'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>

        <xsl:if test="((ROW_TYPE = '5') or (ROW_TYPE = '6'))">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat(' + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="font" select="'reverse-normal'"/>
                    <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
            <xsl:if test="QUANTITY &lt; 0">
              <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(' - ', substring(DESCRIPTION, 1, 37))"/>
                <xsl:with-param name="font" select="'reverse-normal'"/>
                <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
        </xsl:if>

        <xsl:if test="ARTICLE_NOTES != ''"> <!-- libere -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                <xsl:with-param name="font" select="'reverse-underline'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="((ROW_TYPE = '7') or (ROW_TYPE = '8') or (ROW_TYPE = '9') or (ROW_TYPE = '10'))">
            <!-- libere -->
            <!-- xsl:if test="ARTICLE_NOTES != ''">
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                    <xsl:with-param name="font" select="'reverse-underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if -->
            <xsl:if test="RECORD_TYPE = 3"> <!-- codificate -->
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(DESCRIPTION, 1, 38))"/>
                    <xsl:with-param name="font" select="'reverse-underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if>
        </xsl:if>
      </xsl:if>
      
    </xsl:for-each>
  </xsl:if>

  <!-- Cycle for following courses -->
  <xsl:if test="table/record/child_records/table[@name='Ordinazione articoli per altre portate']/record != ''">
    <xsl:for-each select="table/record/child_records/table[@name='Ordinazione articoli per altre portate']/record">
      <xsl:if test="(not(preceding-sibling::record[1]) or (COURSE_DESC != preceding-sibling::record[1]/COURSE_DESC))">
        <xsl:call-template name="PrintHeadingText">
          <xsl:with-param name="value" select="concat('Segue ', COURSE_DESC)"/>
          <xsl:with-param name="font" select="'bold'"/>
          <xsl:with-param name="length" select="40"/>
        </xsl:call-template>
      </xsl:if>

      <xsl:if test="DELETED=0">
        <!-- xsl:if test="ROW_TYPE != '3'">
            <xsl:if test="(not(preceding-sibling::record[1]) or (MENU_DESC != preceding-sibling::record[1]/MENU_DESC))">
        <xsl:if test="ROW_TYPE = '3'" -->
            <xsl:if test="(not(preceding-sibling::record[1]) or (MASTER_MENU != preceding-sibling::record[1]/MASTER_MENU))">
                <xsl:if test="MASTER_MENU != ''">
                    <xsl:if test="string-length(MASTER_MENU) &gt; 30">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat('       (',substring(MASTER_MENU,1,30),')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                    <xsl:if test="string-length(MASTER_MENU) &lt; 31">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat(substring('                                      ',1,38 - string-length(MASTER_MENU)), '(',MASTER_MENU,')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                </xsl:if>
        </xsl:if>
            <!-- /xsl:if>
        </xsl:if -->

        <xsl:if test="((ROW_TYPE = '2') or (ROW_TYPE = '4'))">
            <!-- Articolo -->
            <!-- xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')"/>
                <xsl:with-param name="font" select="'bold'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(DESCRIPTION, 1, 40)"/>
                <xsl:with-param name="font" select="'bold'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(concat(format-number(QUANTITY, '#0', 'currency'), '  ', DESCRIPTION), 1, 40)"/>
                <xsl:with-param name="font" select="'bold'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="((ROW_TYPE = '5') or (ROW_TYPE = '6'))">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat(' + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="font" select="'normal'"/>
                    <xsl:with-param name="length" select="40"/>
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

        <xsl:if test="ARTICLE_NOTES != ''"> <!-- libere -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                <xsl:with-param name="font" select="'underline'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="((ROW_TYPE = '7') or (ROW_TYPE = '8') or (ROW_TYPE = '9') or (ROW_TYPE = '10'))">
            <!-- libere -->
            <!-- xsl:if test="ARTICLE_NOTES != ''">
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                    <xsl:with-param name="font" select="'underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if -->
            <xsl:if test="RECORD_TYPE = 3"> <!-- codificate -->
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(DESCRIPTION, 1, 38))"/>
                    <xsl:with-param name="font" select="'underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if>
        </xsl:if>

      </xsl:if>
      <xsl:if test="DELETED=1">

        <!-- xsl:if test="ROW_TYPE != '3'">
            <xsl:if test="(not(preceding-sibling::record[1]) or (MENU_DESC != preceding-sibling::record[1]/MENU_DESC))">
        <xsl:if test="ROW_TYPE != '3'" -->
            <xsl:if test="(not(preceding-sibling::record[1]) or (MASTER_MENU != preceding-sibling::record[1]/MASTER_MENU))">
                <xsl:if test="MASTER_MENU != ''">
                    <xsl:if test="string-length(MASTER_MENU) &gt; 30">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat('       (',substring(MASTER_MENU,1,30),')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                    <xsl:if test="string-length(MASTER_MENU) &lt; 31">
                        <xsl:call-template name="PrintDetailText">
                            <xsl:with-param name="value" select="concat(substring('                                      ',1,38 - string-length(MASTER_MENU)), '(',MASTER_MENU,')' )"/>
                            <xsl:with-param name="font" select="'normal'"/>
                            <xsl:with-param name="length" select="40"/>
                        </xsl:call-template>
                    </xsl:if>
                </xsl:if>
        </xsl:if>
            <!-- /xsl:if>
        </xsl:if -->

        <xsl:if test="((ROW_TYPE = '2') or (ROW_TYPE = '4'))">
            <!-- Articolo -->
            <!-- xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ')"/>
                <xsl:with-param name="font" select="'reverse-bold'"/>
                <xsl:with-param name="length" select="20"/>
            </xsl:call-template>
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(DESCRIPTION, 1, 40)"/>
                <xsl:with-param name="font" select="'reverse-bold'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="substring(concat(format-number(QUANTITY, '#0', 'currency'), '  ', DESCRIPTION), 1, 40)"/>
                <xsl:with-param name="font" select="'reverse-bold'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="((ROW_TYPE = '5') or (ROW_TYPE = '6'))">
            <!-- variante -->
            <xsl:if test="QUANTITY &gt; 0">
              <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat(' + ', substring(DESCRIPTION, 1, 37))"/>
                    <xsl:with-param name="font" select="'reverse-normal'"/>
                    <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
            <xsl:if test="QUANTITY &lt; 0">
              <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat(' - ', substring(DESCRIPTION, 1, 37))"/>
                <xsl:with-param name="font" select="'reverse-normal'"/>
                <xsl:with-param name="length" select="40"/>
              </xsl:call-template>
            </xsl:if>
        </xsl:if>

        <xsl:if test="ARTICLE_NOTES != ''"> <!-- libere -->
            <xsl:call-template name="PrintDetailText">
                <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                <xsl:with-param name="font" select="'reverse-underline'"/>
                <xsl:with-param name="length" select="40"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="((ROW_TYPE = '7') or (ROW_TYPE = '8') or (ROW_TYPE = '9') or (ROW_TYPE = '10'))">
            <!-- libere -->
            <!-- xsl:if test="ARTICLE_NOTES != ''">
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))"/>
                    <xsl:with-param name="font" select="'reverse-underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if -->
            <xsl:if test="RECORD_TYPE = 3"> <!-- codificate -->
                <xsl:call-template name="PrintDetailText">
                    <xsl:with-param name="value" select="concat('  ', substring(DESCRIPTION, 1, 38))"/>
                    <xsl:with-param name="font" select="'reverse-underline'"/>
                    <xsl:with-param name="length" select="40"/>
                </xsl:call-template>
            </xsl:if>
        </xsl:if>

      </xsl:if>
    </xsl:for-each>
  </xsl:if>
  <empty_row/>
  <empty_row/>
  <empty_row/>
  <empty_row/>
  <empty_row/>
  <xsl:element name="beeper"/>
  <xsl:element name="close_print"/>
  </xsl:element>
</xsl:template>  
</xsl:stylesheet>
