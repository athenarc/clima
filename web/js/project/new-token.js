const SOURCE_COPY_EVENT_REGISTRY = {};


const notifyCopied = (element) => {
    const headerElements = element.getElementsByClassName("header");
    if (headerElements.length > 0) {
        const headerElement = headerElements[0];
        if (SOURCE_COPY_EVENT_REGISTRY.hasOwnProperty(element.id)) {
            clearTimeout(SOURCE_COPY_EVENT_REGISTRY[element.id]);
        }
        headerElement.innerText = "Copied!";
        SOURCE_COPY_EVENT_REGISTRY[element.id] = setTimeout(() => {
            headerElement.innerText = "Copy";
            delete SOURCE_COPY_EVENT_REGISTRY[element.id];
        }, 2000);
    }
}

const assignCopyHandler = element => {
    const copyButtons = element.getElementsByClassName("header");
    if (copyButtons.length > 0) {
        const copyButton = copyButtons[0];
        const handleCopyEvent = event => {
            const contentElements = element.getElementsByClassName("content");
            if (contentElements.length > 0) {
                const contentElement = contentElements[0];
                const text = contentElement.innerText;
                navigator.clipboard.writeText(text);
                notifyCopied(element);
            }
        };
        copyButton.addEventListener("click", handleCopyEvent);
    }
    return element;
}

const generateSourceContentContainer = (parent, content, id, useCopy = false) => {
    const outerContainer = document.createElement("div");
    outerContainer.className = "source-content"
    outerContainer.classList = outerContainer.className.split();

    outerContainer.id = id;

    const contentContainer = document.createElement("div");
    contentContainer.className = "content";
    contentContainer.classList = contentContainer.className.split();

    contentContainer.innerText = content;

    const headerContainer = (useCopy ? document.createElement("div") : null);
    if (headerContainer) {
        headerContainer.className = "header";
        headerContainer.classLise = headerContainer.className.split();

        headerContainer.innerText = "Copy";

        outerContainer.appendChild(headerContainer);
    }
    outerContainer.appendChild(contentContainer)

    return outerContainer;
}