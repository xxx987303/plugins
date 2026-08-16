/**
 * Keyword Tooltip Script for WordPress
 * Add to your theme's functions.php via wp_enqueue_scripts,
 * or paste into a Custom HTML block / child theme's JS file.
 *
 * Usage: edit the `keywords` object below, then enqueue this file.
 */

(function () {
  // ─── Configure your keywords and definitions here ───────────────────────────
  const keywords = {
      "санэпидстанции": "Санитарная Эпидиомиологическая Станция",
  };

  // ─── CSS selector: which containers to scan for keywords ─────────────────────
  // ".entry-content" targets standard post/page content. Add more selectors
  // as needed, e.g. ".woocommerce-product-details__short-description".
  const SCAN_SELECTOR = ".entry-content";
  // ─────────────────────────────────────────────────────────────────────────────

  const STYLE = `
    .kw-tooltip-highlight {
      background: #e8f0fe;
      color: #1a56db;
      border-bottom: 1px dashed #1a56db;
      border-radius: 3px;
      padding: 0 2px;
      cursor: default;
      font-weight: 500;
    }
    #kw-tooltip-bubble {
      position: absolute;
      display: none;
      max-width: 260px;
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 13px;
      line-height: 1.5;
      color: #111827;
      pointer-events: none;
      z-index: 99999;
      box-shadow: 0 4px 12px rgba(0,0,0,0.10);
    }
  `;

  function injectStyles() {
    const el = document.createElement("style");
    el.textContent = STYLE;
    document.head.appendChild(el);
  }

  function createTooltip() {
    const el = document.createElement("div");
    el.id = "kw-tooltip-bubble";
    document.body.appendChild(el);
    return el;
  }

  function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  /**
   * Walks text nodes inside `root` and wraps keyword matches in <span> elements.
   * Text-node walking avoids breaking HTML tags or double-wrapping.
   */
  function wrapKeywordsInNode(root, kwMap, tooltip) {
    const sorted = Object.keys(kwMap).sort((a, b) => b.length - a.length);
    const pattern = new RegExp(`(${sorted.map(escapeRegex).join("|")})`, "g");

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode(node) {
        const parent = node.parentElement;
        if (!parent) return NodeFilter.FILTER_REJECT;
        const tag = parent.tagName.toUpperCase();
        // Skip script, style, code, pre, and already-highlighted spans
        if (["SCRIPT", "STYLE", "CODE", "PRE", "TEXTAREA"].includes(tag)) return NodeFilter.FILTER_REJECT;
        if (parent.classList.contains("kw-tooltip-highlight")) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    });

    const textNodes = [];
    let node;
    while ((node = walker.nextNode())) textNodes.push(node);

    textNodes.forEach(textNode => {
      const text = textNode.textContent;
      if (!pattern.test(text)) return;
      pattern.lastIndex = 0;

      const frag = document.createDocumentFragment();
      let last = 0;
      let match;

      while ((match = pattern.exec(text)) !== null) {
        if (match.index > last) {
          frag.appendChild(document.createTextNode(text.slice(last, match.index)));
        }
        const span = document.createElement("span");
        span.className = "kw-tooltip-highlight";
        span.textContent = match[0];
        span.setAttribute("data-kw", match[0]);

        span.addEventListener("mouseenter", () => {
          tooltip.textContent = kwMap[match[0]] || "";
          tooltip.style.display = "block";
        });
        span.addEventListener("mousemove", e => {
          tooltip.style.left = (e.pageX + 14) + "px";
          tooltip.style.top  = (e.pageY + 14) + "px";
        });
        span.addEventListener("mouseleave", () => {
          tooltip.style.display = "none";
        });

        frag.appendChild(span);
        last = match.index + match[0].length;
      }

      if (last < text.length) {
        frag.appendChild(document.createTextNode(text.slice(last)));
      }

      textNode.parentNode.replaceChild(frag, textNode);
    });
  }

  function init() {
    injectStyles();
    const tooltip = createTooltip();
    document.querySelectorAll(SCAN_SELECTOR).forEach(container => {
      wrapKeywordsInNode(container, keywords, tooltip);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
