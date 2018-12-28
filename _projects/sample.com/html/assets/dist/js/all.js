
;
;
(function () {

    var getCustomProperty = function(property) {
        var style = getComputedStyle(document.documentElement);
        return String(style.getPropertyValue(property)).trim();
    };
    
    var setCustomProperty = function(property, default_value) {
        var value = getCustomProperty(property);
        return value ? value : default_value;
    };

    var default_theme_settings = {
        base_size: setCustomProperty('--base-size', '16px'),
        breakpoint_mm: setCustomProperty('--breakpoint-mm', '0'),
        breakpoint_sp: setCustomProperty('--breakpoint-sp', '480px'),
        breakpoint_tb: setCustomProperty('--breakpoint-tb', '768px'),
        breakpoint_dt: setCustomProperty('--breakpoint-dt', '960px'),
        breakpoint_dt_md: setCustomProperty('--breakpoint-dt-md', '1024px'),
        breakpoint_dt_lg: setCustomProperty('--breakpoint-dt-lg', '1280px'),
        breakpoint_dt_xl: setCustomProperty('--breakpoint-dt-xl', '1366px'),
        breakpoint_mx: setCustomProperty('--breakpoint-mx', '1440px'),
    };

    // windowに追加
    window.default_theme_settings = default_theme_settings;
})();;

(function () {

    // Common setting
    var d = document;
    var w = window;

    var prevAll = function (node, selector) {
        var list = [];
        var prev = node.previousElementSibling;
        while (prev && prev.nodeType === 1) {
            list.unshift(prev);
            prev = prev.previousElementSibling;
        }
        if (selector) {
            var node = [].slice.call(document.querySelectorAll(selector));
            list = list.filter(function (item) {
                return node.indexOf(item) !== -1;
            });
        }
        return list;
    }

    // Table hover rows
    var table = d.querySelectorAll('table.table-hover');
    if (table.length) {

        // Init
        table.forEach(function (table, index) {
            var tables = table.querySelectorAll('tr');
            tables.forEach(function (element, index) {
                var rowspan = element.querySelectorAll('td[rowspan]');
                if (rowspan.length) {
                    var row_length = rowspan[0].getAttribute('rowspan');
                    for (var i = 0; i < row_length; ++i) {
                        var tr = tables[index + i];
                        if (i === 0) {
                            tr.classList.add('tr-rowspan');
                            tr.setAttribute('data-row', index);
                        } else {
                            tr.classList.add('tr-sibling');
                            tr.setAttribute('data-sibling', index);
                        }
                    }
                }
            });
        });

        // Action
        var siblings = d.querySelectorAll('table.table-hover tr.tr-sibling td');
        siblings.forEach(function (td) {
            td.addEventListener('mouseover', function () {
                var _this = this.parentNode;
                var sibling_index = _this.getAttribute('data-sibling');
                prevAll(_this, 'tr[data-row="' + sibling_index + '"]')[0].classList.add('tr-on');
            });
            td.addEventListener('mouseout', function () {
                var _this = this.parentNode;
                var sibling_index = _this.getAttribute('data-sibling');
                prevAll(_this, 'tr[data-row="' + sibling_index + '"]')[0].classList.remove('tr-on');
            });
        });
    }
})();

;

// Extension
// Extension
(function () {
    "use strict";

    var d = document;
    var w = window;

    // View height
    var s = d.documentElement.style;
    if (s.setProperty !== "undefined") {
        // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
        let vh = w.innerHeight * 0.01;
        // Then we set the value in the --vh custom property to the root of the document
        s.setProperty("--vh", "${vh}px");

        // We listen to the resize event
        w.addEventListener("resize", function () {
            // We execute the same script as before
            let vh = w.innerHeight * 0.01;
            s.setProperty("--vh", "${vh}px");
        });
    }
})();
;
(function () {
    "use strict";

    // Common setting
    var d = document;
    var w = window;

    var slideButton = d.querySelectorAll('.slide-button');
    if (slideButton.length) {
        d.querySelectorAll('.slide-button')[0].addEventListener('click', function (e) {
            d.body.classList.toggle('open-slidemenu');
            return false;
        });
    }

    var content = d.getElementById('contents');
    if (content != null) {
        d.getElementById('content').addEventListener('click', function (e) {
            d.body.classList.remove('open-slidemenu');
        });
    }
})();;
(function () {
    "use strict";

    var d = document;
    var w = window;
    var duration = 400;

    // Easing easeOutQuad (http://gsgd.co.uk/sandbox/jquery/easing/)
    var easing = function (t, b, c, d) {
        return c * (t /= d) * t * t + b;
    };

    // 'requestAnimationFrame'が使用できる場合
    if (w.hasOwnProperty('requestAnimationFrame')) {

        // DOM終了後
        d.addEventListener('DOMContentLoaded', function () {

            // ページ内スクロールの指定がある場合
            var elements = d.querySelectorAll('a[href^="#"]');
            if (elements.length) {

                // 全ターゲットにイベントを指定
                Array.prototype.forEach.call(elements, function (element) {
                    element.addEventListener('click', function (e) {
                        // イベント無効
                        e.preventDefault();

                        // 空の場合はスルー
                        if (!this.hash) return;

                        // 移動先がない場合はスルー
                        var target = d.querySelectorAll(this.hash);
                        if (!target) return;
                        

                        // ターゲットの場所を計測
                        var targetPos = target[0].getBoundingClientRect().top;

                        // アニメーションの指定
                        var startTime = Date.now();
                        var scrollFrom = scrollPosition.scrollTop;

                        // Anime.jsがあればそちらを利用（個人的に使ってるので）
                        if ('anime' in w) {
                            var scrollTo = anime({
                                targets: 'html, body',
                                scrollTop: targetPos + scrollFrom,
                                duration: duration,
                                easing: 'easeOutQuad'
                            });
                        }

                        // アニメーション
                        else {
                            (function loop() {
                                var currentTime = Date.now() - startTime;
                                if (currentTime < duration) {
                                    scrollTo(0, easing(currentTime, scrollFrom, targetPos, duration));
                                    window.requestAnimationFrame(loop);
                                } else {
                                    scrollTo(0, targetPos + scrollFrom);
                                }
                            })();
                        }

                    });
                });
            }
        }, false);

    }

    var scrollPosition = (function () {
        if ('scrollingElement' in document) return document.scrollingElement;
        if (navigator.userAgent.indexOf('WebKit') != -1) return document.body;
        return document.documentElement;
    })();

})();;;;