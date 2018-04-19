<div class="center">
    <h3>Введите url сайта</h3>
    <form method="post">
        <input type="text" name="url" id="url">
        <input type="submit" value="Проверить">
    </form>
    {% if not(file_name is empty)%}
        Ссылка на скачивание <a href="/download?file_name={{ file_name }}">{{ site_name }}/download?file_name={{ file_name }}</a>
    {% endif %}
</div>
{% if not(site_parse is empty)%}
    <h3>Адрес ресурса {{ site_parse }}</h3>
{% endif %}
{% if not(answer_array is empty)%}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>№</th>
                <th>Название проверки</th>
                <th>Статус</th>
                <th></th>
                <th>Текущее состояние</th>
            </tr>
        </thead>
        <tbody>
        {% for index, v in answer_array %}
            <tr>
                <td rowspan="2">{{ index }}</td>
                <td rowspan="2">{{ v['name'] }}</td>
                {% if v['status'] === "ok" %}
                    <td rowspan="2" style="background-color: green">OK</td>
                {% else %}
                    <td rowspan="2" style="background-color: red">Ошибка</td>
                {% endif %}
                <td>Состояние</td>
                <td>{{ v['situation'] }}</td>
            </tr>
            <tr>
                <td>Рекомендации</td>
                <td>{{ v['recommendation'] }}</td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
{% endif %}