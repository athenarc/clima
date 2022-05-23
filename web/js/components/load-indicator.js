const doesLabelFit = loadingBar => {
    return  loadingBar.scrollWidth<=loadingBar.clientWidth && loadingBar.scrollHeight<=loadingBar.clientHeight;
}

const resetLoadIndicatorTooltips = () => {
    document.querySelectorAll('.load-indicator .progress-bar').forEach(progressBar=>{
        const el = progressBar.querySelector('span');
        if (doesLabelFit(el)) {
            el.style.visibility='visible';
        }
        else {
            el.style.visibility='hidden';
        }
    })
}

let resetLoadIndicatorTooltipsOnTimeout = null;

window.addEventListener('load', ()=>{
    $('.load-indicator .progress-bar').tooltip();
    resetLoadIndicatorTooltips();
})

window.addEventListener('resize', ()=>{
    // Reset load indicator tooltips if for 200ms no resize has occurred
    // Used to avoid resets on every single resize event triggered
    clearTimeout(resetLoadIndicatorTooltipsOnTimeout);
    resetLoadIndicatorTooltipsOnTimeout = setTimeout(resetLoadIndicatorTooltips, 200);
})