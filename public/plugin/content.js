// content.js
(async () => {
    if (!window.location.href.includes("www.leboncoin.fr")) return;

    let visitedPages = new Set();

    async function scrapePage(url) {
        try {
            if (visitedPages.has(url)) return []; // Ne pas repasser sur une page déjà visitée
            visitedPages.add(url);

            const response = await fetch(url);
            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, "text/html");

            // Vérifier s'il y a encore des articles
            const articles = doc.querySelectorAll("article");
            if (articles.length === 0) return [];
            let pages = [];

            const brands = document.querySelector("button[aria-label='Ouvrir le filtre Marque']").title;
            const models = document.querySelector("button[aria-label='Ouvrir le filtre Modèle']").title;
            for (const article of articles) {
                const linkElement = article.querySelector("a");
                if (!linkElement) continue;

                const normalizeString = (str) =>
                    str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                const title = article.ariaLabel;
                const postcode = article.querySelector("p[aria-label^='Située à']").ariaLabel.replace(/\D/g,'');
                const normalizeTitle = normalizeString(title).toLowerCase();
                const brandArray = brands.split(", ").map(b => b.trim().toLowerCase());
                const brand = brandArray.find(brand => normalizeTitle.includes(brand));
                const modelArray = models.split(", ").map(b => b.trim().toLowerCase());
                const model = modelArray.find(model => normalizeTitle.includes(model));

                const priceElement = article.querySelector("p[data-test-id='price']");
                const price = priceElement.ariaLabel;
                const link = linkElement.href;
                if (visitedPages.has(link)) continue;
                visitedPages.add(link);

                const adParamsContainer = article.querySelector("div[data-test-id='ad-params-labels']");
                let adParams = [];
                if (adParamsContainer) {
                    adParams = [...adParamsContainer.children].map(div => div.innerText.trim());
                }

                pages.push({ url: link, title, brand, model, price, postcode, adParams });
            }

            //Cette partie permet daller chercher des infos plus detailles sur chaque page produit
            /*
            const links = [...articles].map(a => a.href);
            for (const link of links) {
                try {
                    const response = await fetch(link);
                    const text = await response.text();
                    const subDoc = parser.parseFromString(text, "text/html");
                    const criteriaContainer = subDoc.querySelector("div[data-qa-id='criteria_container']");
                    const content = criteriaContainer ? criteriaContainer.innerHTML : "Content not found";

                    const titleElement = subDoc.querySelector("h1.text-headline-1-expanded");
                    const priceElement = subDoc.querySelector("div[data-qa-id='adview_price'] p.text-headline-2");

                    const title = titleElement ? titleElement.innerText.trim() : "";
                    const price = priceElement ? priceElement.innerText.trim() : "0";

                    pages.push({ url: link, content, title, price });
                } catch (error) {
                    console.error(`Error fetching ${link}:`, error);
                }
            }
            */

            // Trouver les liens de pagination et les suivre
            const paginationLinks = [...doc.querySelectorAll("a[data-spark-component=pagination-item]")]
                .map(a => a.href)
                .filter(href => href);

            for (const pageLink of paginationLinks) {
                const nextPages = await scrapePage(pageLink);
                pages = pages.concat(nextPages);
            }

            return pages;
        } catch (error) {
            console.error(`Error fetching ${url}:`, error);
            return [];
        }
    }


    const currentUrl = window.location.href;
    const scrapedData = await scrapePage(currentUrl);

    if (scrapedData.length > 0) {

        const currentDate = new Date();
        const formattedDate = formatDate(currentDate);

        const blob = new Blob([JSON.stringify(scrapedData, null, 2)], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "scraped_articles_"+formattedDate+".json";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }


    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day}-${hours}:${minutes}:${seconds}`;
    }
})();
