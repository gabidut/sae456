document.getElementById('departement-input').addEventListener('input', function () {
    const input = this.value;
    const datalist = document.getElementById('villes');

    datalist.innerHTML = '';
    fetch(`http://localhost/api/cities.php?citiesByDep=${input}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(city => {
                const option = document.createElement('option');
                option.value = city.COM_NOM;
                datalist.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching cities:', error));
});