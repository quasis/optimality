(function(parent) {

    const forEach = Array.prototype.forEach, toggle = function(inputs, status) {

        forEach.call(inputs, function(object) { object.readOnly = status });
    };

    var   active  = parent.querySelector(window.location.hash ?
        'a[href="' + window.location.hash + '"]' : 'a[class="nav-tab"]');

    location.replace(active.href); active.classList.add('nav-tab-active');

    forEach.call(parent.querySelectorAll('.nav-tab'), function(header) {

        header.addEventListener('click', function(event) {

            active.classList.remove('nav-tab-active');
            event.target.classList.add('nav-tab-active');
            active = event.target;
        });
    });

    forEach.call(parent.querySelectorAll('input[type="checkbox"]'), function(master) {

        var slaves = parent.querySelectorAll('input[data-parent="' + master.name + '"]');

        toggle(slaves, !master.checked);
        master.addEventListener('change', function() { toggle(slaves, !master.checked) });
    });

    forEach.call(parent.querySelectorAll('[data-action]'), function(button) {

        button.addEventListener('click', function(event) {

            var action = button.getAttribute('data-action');
            var output = button.querySelector('output');
            var symbol = button.querySelector('span[class^="dashicons"]');

            symbol.classList.add('spin');

            jQuery.post(action, { }, function(result) {

                output.innerText = Math.max(0, parseInt(output.innerText) - result);
                button.disabled  = output.innerText <= 0;
                symbol.classList.remove('spin');
            });
        });
    });

})(document.querySelector('form[action="options.php"]'));
