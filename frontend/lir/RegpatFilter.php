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
})();
</script>