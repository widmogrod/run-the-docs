const elementProto = Element.prototype;
const matchesSelector = elementProto.mozMatchesSelector
    || elementProto.webkitMatchesSelector
    || elementProto.oMatchesSelector
    || elementProto.msMatchesSelector;

function eventEmitter(element) {
    return {
        on: function on(event, selector, fn) {
            element.addEventListener(event, function(e) {
                if (matchesSelector.call(e.target, selector)) {
                    fn.call(e.target, e);
                }
            }, false);
        }
    };
}


eventEmitter(document.getElementById('main'))
    .on('click', '[data-action="run-example"]', function(e){
        e.preventDefault();
        fetch(e.target.href)
            .then(function(response) {
                return response.text();
            })
            .then(function(text) {
                document.getElementById(e.target.dataset.id).innerHTML = text;
            });
    });
