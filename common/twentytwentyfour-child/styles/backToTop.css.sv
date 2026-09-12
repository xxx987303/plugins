#backToTop {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    z-index: 9999;
    cursor: pointer;

    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease;
}

#backToTop.visible {
    opacity: 0.8;
    visibility: visible;
}

#backToTop:hover {
    opacity: 1;
}

#backToTop img {
    width: 100%;
    height: auto;
    display: block;
}
