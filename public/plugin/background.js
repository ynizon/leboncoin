// background.js
let hasDownloaded = false;
chrome.action.onClicked.addListener((tab) => {
    if (!hasDownloaded && tab.url.includes("www.leboncoin.fr")) {
        hasDownloaded = true;
        chrome.scripting.executeScript({
            target: { tabId: tab.id },
            files: ["content.js"]
        });
    }
});
