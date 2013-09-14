; Installer for upsilon-node
!include MUI2.nsh
!include Sections.nsh
!include target\project.nsh
!include target\classes\upsilon.nsh

Name "upsilon-node"
OutFile "target/upsilon-node-${UPSILON_NODE_VERSION}.exe"
SetCompressor /SOLID lzma
XPStyle on
CRCCheck on
InstallDir "C:\Program Files\upsilon-node\"
AutoCloseWindow false
ShowInstDetails show
Icon "../../var/logodarkbg.ico"
BrandingText "Upsilon Project"

VIProductVersion ${UPSILON_NODE_WIN_VERSION}
VIAddVersionKey ProductName "upsilon-node"
VIAddVersionKey ProductVersion "${UPSILON_NODE_WIN_VERSION}"
VIAddVersionKey FileVersion "${UPSILON_NODE_WIN_VERSION}"
VIAddVersionKey FileDescription ""
VIAddVersionKey LegalCopyright ""

  !define MUI_CUSTOMFUNCTION_GUIINIT onMyGuiInit
  !define MUI_ABORTWARNING
  !define MUI_COMPONENTSPAGE_SMALLDESC
  
  !define MUI_ICON "../../var/logodarkbg.ico"
  !define MUI_HEADERIMAGE
  !define MUI_HEADERIMAGE_BITMAP "../../var/installer_tr.bmp"
  !define MUI_HEADERIMAGE_RIGHT

  !define MUI_DIRECTORYPAGE_VERIFYONLEAVE

  !define MUI_WELCOMEFINISHPAGE_BITMAP_NOSTRETCH
  !define MUI_WELCOMEFINISHPAGE_BITMAP "../../var/installer_tl.bmp"
  !define MUI_WELCOMEPAGE_TEXT "This is the installer for upsilon-node. This installer assumes you have Java (a JRE) already installed on your system."
  !insertmacro MUI_PAGE_WELCOME
  !insertmacro MUI_PAGE_LICENSE ../../doc/COPYING.txt
  !insertmacro MUI_PAGE_DIRECTORY
  !insertmacro MUI_PAGE_COMPONENTS
  !insertmacro MUI_PAGE_INSTFILES

  !define MUI_FINISHPAGE_TEXT "upsilon-node has been installed. Have a nice day."
  !define MUI_FINISHPAGE_RUN
  !define MUI_FINISHPAGE_RUN_TEXT "Open upsilon-node install and data directory."
  !define MUI_FINISHPAGE_RUN_FUNCTION "LaunchInstallDir"
  !insertmacro MUI_PAGE_FINISH

  !insertmacro MUI_LANGUAGE "English"

Section "upsilon-node"
    SetShellVarContext all
    SetOverwrite on

    CreateDirectory "$SMPROGRAMS\upsilon"

    SetOutPath $INSTDIR
    CreateShortCut "$SMPROGRAMS\upsilon\upsilon Program Directory.lnk" "$INSTDIR\" "" "$INSTDIR\logodarkbg.ico" 0
    File src/main/nsis/README.txt
    File ../upsilon-jar/target/upsilon-node-${UPSILON_NODE_VERSION}.jar
    Rename upsilon-node-${UPSILON_NODE_VERSION}.jar upsilon-node.jar

    File ../../var/logodarkbg.ico

    CreateShortCut "$SMPROGRAMS\upsilon\upsilon Data Directory.lnk" "$APPDATA\upsilon-node\" "" "$INSTDIR\logodarkbg.ico" 0

    CreateDirectory "$APPDATA\upsilon-node\"

    SetOutPath "$APPDATA\upsilon-node\"
    File ../upsilon-jar/var/config.xml.sample
    File ../upsilon-jar/var/logging.windows.xml
    Rename logging.windows.xml logging.xml

    CreateShortCut "$SMPROGRAMS\upsilon\upsilon Website & Documentation.lnk" "http://upsilon-project.co.uk" "" "$INSTDIR\logodarkbg.ico" 0

;    writeUninstaller "$INSTDIR\upsilon-node_uninstall.exe"
SectionEnd

Function LaunchInstallDir
	ExecShell "" "$INSTDIR"
	ExecShell "" "$APPDATA\upsilon-node\"
FunctionEnd

;Section "uninstall"
;   delete "$INSTDIR\pom.xml"
;SectionEnd
;
Function onMyGuiInit
	CreateFont $R1 "Tahoma" 8 800

	GetDlgItem $R0 $HWNDPARENT 1028
	SendMessage $R0 ${WM_SETFONT} $R1 0
	SetCtlColors $R0 FFFFFF FF0000

	GetDlgItem $R0 $HWNDPARENT 1256
	SendMessage $R0 ${WM_SETFONT} $R1 0
	SetCtlColors $R0 FFFFFF FF0000
FunctionEnd

Function .onInit
    InitPluginsDir
	GetDlgItem $R0 $HWNDPARENT 1024
	SetCtlColors $R0 000000 FF0000
FunctionEnd
