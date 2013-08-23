x=0

rm sprites.css
rm sprites.png
rm sprites.html

echo '<link rel = "stylesheet" type = "text/css" href = "sprites.css" />' >> sprites.html
echo '<style type = "text/css">div { text-align: center; margin: .5em; overflow: hidden; display: inline-block; position: relative; width: 200px; height: 70px; border: 1px solid black;}</style>' >> sprites.html
echo ".serviceIcon { display: inline-block; min-width: 16px; min-height: 16px; background-image: url('sprites.png'); background-repeat: no-repeat } " >> sprites.css

for file in *.png; do
	name=`echo $file | sed 's/\.png//g'`

	echo "span.$name { background-position: 0px -${x}px; }" >> sprites.css
	echo "<div>$file <br /><span title = '$name' class = 'serviceIcon $name'></span></div>" >> sprites.html

	let x+=16
done

convert *.png -append sprites.png
