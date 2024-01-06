function copyPlaylistUrl() {
    const playlistUrl = document.getElementById("url").innerText;
    const copiedMessage = document.getElementById("copied-message");

    const type = "text/plain";
    const blob = new Blob([playlistUrl], { type });
    const data = [new ClipboardItem({ [type]: blob })];

    navigator.clipboard.write(data).then(() => {
        console.log("コピーしました");
        copiedMessage.style.visibility = "visible";
        setTimeout(() => {
            copiedMessage.style.visibility = "hidden";
        }, 5000);
    });
}
