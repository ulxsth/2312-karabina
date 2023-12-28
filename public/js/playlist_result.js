function copy(){
	const playlistUrl = document.getElementById("url").innerText;

	const type = "text/plain";
	const blob = new Blob([playlistUrl], {type});
	const data = [new ClipboardItem({ [type]: blob})];

	navigator.clipboard.write(data).then(
		() => console.log("コピーしました"),
	);
	
} 