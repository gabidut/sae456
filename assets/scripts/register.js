document.getElementById('departement-input').addEventListener('input', function () {
    const input = this.value;
    const datalist = document.getElementById('ville');

    datalist.innerHTML = '';
    fetch(`/api/cities.php?citiesByDep=${input}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(city => {
                const option = document.createElement('option');
                option.value = city.COM_NOM;
                option.textContent = city.COM_NOM;
                datalist.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching cities:', error));
});