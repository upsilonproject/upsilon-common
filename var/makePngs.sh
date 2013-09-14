convert -background "#444444" logo.svg logo1000pxdarkbg.png
convert -background "#444444" -resize 16x16 logo.svg logo16pxdarkbg.png
convert -background "#444444" -resize 32x32 logo.svg logo32pxdarkbg.png
convert -background "#444444" -resize 48x48 logo.svg logo48pxdarkbg.png
convert -background "#444444" -resize 64x64 logo.svg logo64pxdarkbg.png
convert -background "#444444" -resize 72x72 logo.svg logo72pxdarkbg.png
convert -background "#444444" -resize 96x96 logo.svg logo96pxdarkbg.png
convert -background "#444444" -resize 128x128 logo.svg logo128pxdarkbg.png
convert -background "#444444" -resize 256x256 logo.svg logo256pxdarkbg.png
convert -background "#444444" -resize 512x512 logo.svg logo512pxdarkbg.png
convert -background transparent logo.svg logo1000pxtransparentbg.png

#convert logo16pxdarkbg.png logo48pxdarkbg.png logo64pxdarkbg.png logo128pxdarkbg.png logo512pxdarkbg.png logodarkbg.ico
convert logo16pxdarkbg.png logo32pxdarkbg.png logo48pxdarkbg.png logodarkbg.ico

#convert -background "#444444" -resize 150x57 logo.svg installer.bmp
