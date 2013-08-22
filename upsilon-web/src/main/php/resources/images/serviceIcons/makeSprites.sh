x=0

rm sprites.css
rm sprites.png
rm sprites.html

echo '<link rel = "stylesheet" type = "text/css" href = "sprites.css" />' >> sprites.html
echo '<style type = "text/css">div { text-align: center; margin: .5em; overflow: hidden; display: inline-block; position: relative; width: 200px; height: 70px; border: 1px solid black;}</style>' >> sprites.html

for file in *.png; do
	name=`echo $file | sed 's/\.png//g'`

	echo "img.$name { background-image: url('sprites.png'); background-position: 0px -${x}px; background-repeat: no-repeat;}" >> sprites.css
	echo "<div>$file <br /><img width = "16" height = "16" class = '$name' /></div>" >> sprites.html

	let x+=16
done

convert *.png -append sprites.png
