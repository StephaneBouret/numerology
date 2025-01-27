const scrollToggleDiv = () => {    
    const targetDiv = document.querySelector('.xya3m1');
    if (targetDiv) {        
        window.addEventListener('scroll', () => {
            const scrollPosition = window.scrollY;            
            if (scrollPosition > 50) {
                targetDiv.classList.remove("xya3m1");
                targetDiv.classList.add("i3u89z");
            } else {
                targetDiv.classList.remove("i3u89z");
                targetDiv.classList.add("xya3m1");
            }

        })
    }
}

document.addEventListener('load', scrollToggleDiv);
document.addEventListener('turbo:load', scrollToggleDiv);