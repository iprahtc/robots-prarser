<div class="center">
    <h3>Введите url сайта</h3>
    <form method="post">
        <input type="text" name="url" id="url">
        <input type="submit">
    </form>
    {% if not(file_name is empty)%}
        <a href="/download?file_name={{ file_name }}">Скачать файл</a>
    {% endif %}
</div>
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