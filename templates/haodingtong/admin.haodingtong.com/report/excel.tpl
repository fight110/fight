<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Diana</Author>
  <LastAuthor>Diana</LastAuthor>
  <Created>{%$smarty.now|date_format:"%Y-%m-%dT%H:%M:%SZ"%}</Created>
  <LastSaved>{%$smarty.now|date_format:"%Y-%m-%dT%H:%M:%SZ"%}</LastSaved>
  <Version>11.5606</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>12495</WindowHeight>
  <WindowWidth>16035</WindowWidth>
  <WindowTopX>0</WindowTopX>
  <WindowTopY>105</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center"/>
   <Borders/>
   <Font ss:FontName="宋体" x:CharSet="134" ss:Size="12"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s21">
   <Font ss:FontName="宋体" x:CharSet="134" ss:Size="18" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s29">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s35">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="@"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Sheet1">
  <Table>
   <Column ss:AutoFitWidth="0" ss:Width="36"/>
   <Column ss:AutoFitWidth="0" ss:Width="69"/>
   <Column ss:AutoFitWidth="0" ss:Width="78.75"/>
   <Row ss:AutoFitHeight="0">
    {%if $show.k_rownum%}<Cell ss:StyleID="s29"><Data ss:Type="String">序号</Data></Cell>{%/if%}
    {%if $show.k_username%}<Cell ss:StyleID="s29"><Data ss:Type="String">帐号</Data></Cell>{%/if%}
    {%if $show.k_name%}<Cell ss:StyleID="s29"><Data ss:Type="String">客户名</Data></Cell>{%/if%}
    {%if $show.k_location%}<Cell ss:StyleID="s29"><Data ss:Type="String">区域</Data></Cell>{%/if%}

    {%if $show.k_series%}<Cell ss:StyleID="s29"><Data ss:Type="String">{%$keyword.series_id%}</Data></Cell>{%/if%}
    {%if $show.k_wave%}<Cell ss:StyleID="s29"><Data ss:Type="String">{%$keyword.wave_id%}</Data></Cell>{%/if%}
    {%if $show.k_category%}<Cell ss:StyleID="s29"><Data ss:Type="String">{%$keyword.category_id%}</Data></Cell>{%/if%}
    {%if $show.k_style%}<Cell ss:StyleID="s29"><Data ss:Type="String">{%$keyword.style_id%}</Data></Cell>{%/if%}
    {%if $show.k_pname%}<Cell ss:StyleID="s29"><Data ss:Type="String">款名</Data></Cell>{%/if%}
    <Cell ss:StyleID="s29"><Data ss:Type="String">款号</Data></Cell>
    <Cell ss:StyleID="s29"><Data ss:Type="String">颜色</Data></Cell>
    
    {%if $show.k_huohao%}<Cell ss:StyleID="s29"><Data ss:Type="String">货号</Data></Cell>{%/if%}
    
    {%if $show.k_designer%}<Cell ss:StyleID="s29"><Data ss:Type="String">设计师</Data></Cell>{%/if%}
    {%if $show.k_price%}<Cell ss:StyleID="s29"><Data ss:Type="String">价格</Data></Cell>{%/if%}
    {%section name=j loop=$show.size_list%}
    <Cell ss:StyleID="s29"><Data ss:Type="String">{%$show.size_list[j].keywords.name%}</Data></Cell>
    {%/section%}
   </Row>

   {%section name=i loop=$show.list%}
   <Row ss:AutoFitHeight="0">
      {%if $show.k_rownum%}<Cell ss:StyleID="s29"><Data ss:Type="Number">{%$smarty.section.i.rownum%}</Data></Cell>{%/if%}
      {%if $show.k_username%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].username%}</Data></Cell>{%/if%}
      {%if $show.k_name%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].name%}</Data></Cell>{%/if%}
      {%if $show.k_location%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].location.name%}</Data></Cell>{%/if%}
      {%if $show.k_series%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].series.name%}</Data></Cell>{%/if%}
      {%if $show.k_wave%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].wave.name%}</Data></Cell>{%/if%}
      {%if $show.k_category%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].category.name%}</Data></Cell>{%/if%}
      {%if $show.k_style%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].style.name%}</Data></Cell>{%/if%}
      {%if $show.k_pname%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].pname%}</Data></Cell>{%/if%}
      <Cell ss:StyleID="s29"><Data ss:Type="Number">{%$show.list[i].kuanhao%}</Data></Cell>
      <Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].color.name%}</Data></Cell>
      
      {%if $show.k_huohao%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].huohao%}</Data></Cell>{%/if%}
      
      {%if $show.k_designer%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].designer%}</Data></Cell>{%/if%}
      {%if $show.k_price%}<Cell ss:StyleID="s35"><Data ss:Type="String">{%$show.list[i].pprice%}</Data></Cell>{%/if%}
      {%section name=j loop=$show.list[i].size_list%}
      <td></td><Cell ss:StyleID="s35"><Data ss:Type="Number">{%$show.list[i].size_list[j].num%}</Data></Cell>
      {%/section%}
    </Row>
    {%/section%}

  </Table>
 </Worksheet>
</Workbook>