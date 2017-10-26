<script>
(function(){
    var selectRegpat = function(){
        regpat_selected = this.value;
        options = document.querySelectorAll('#database option');
        document.getElementById('database').value = '';
        for (var i = 0; i < options.length; i++) {
            op = options[i];
            if (op.id == 'all')
            {
                continue;
            }

            op_regpats = op.attributes.getNamedItem('data-regpat').value.split(',');
            if (op_regpats.indexOf(regpat_selected) != -1) {
                op.style.display = 'block';
            } else {
                op.style.display = 'none';
            }
            // console.log(op.style.display, op.value, op_regpats)
        }
    };
    document.getElementById('regpat').addEventListener('change', selectRegpat);
    selectRegpat();
    
    // Pick dates helper
    var changeEndDate = function() {
        var date_end = document.getElementById('date_end')
        if (!date_end.hasAttribute('changed'))
            date_end.value = this.value
    };
    document.getElementById('date_end').addEventListener('change', function(){
        this.setAttribute('changed', 'changed')
    });
    document.getElementById('date_begin').addEventListener('change', changeEndDate);
})();
</script>