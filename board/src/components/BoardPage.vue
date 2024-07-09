<template>
  <div class="board-page">
    <div class="board-header">
      <div class="fixed-bar">
        <div class="board-controls">
          <button @click="createColumn" class="create-column-btn">Создать колонку</button>
          <button @click="toggleEditMode" class="edit-columns-btn">
            {{ isEditMode ? 'Сохранить порядок' : 'Изменить порядок' }}
          </button>
        </div>
        <div class="user-info">
          <div class="user-details">
            <div class="user-name">{{ loggedInUser }}</div>
            <img src="../icons/logout.svg" alt="Logout" class="logout-icon" @click="logout">
          </div>
        </div>
      </div>
    </div>
    <draggable v-model="columns" :disabled="!isEditMode" @end="updateOrder" group="columns" class="board-columns" itemKey="column_id">
    <template #item="{ element, index }">
      <TaskColumn :key="element.column_id" :column="element" :index="index"
                  @updateColumn="updateColumn(index, $event)" 
                  @deleteColumn="deleteColumn(index)"
                  @updateCards="updateCards(index, $event)"
                  @moveCard="moveCard($event)" />
    </template>
  </draggable>
  </div>
</template>


<script>
import TaskColumn from './TaskColumn.vue';
import draggable from 'vuedraggable';
import axios from 'axios';

export default {
  name: 'BoardPage',
  components: {
    TaskColumn,
    draggable,
  },
  data() {
    return {
      columns: [],
      isEditMode: false,
      loggedInUser: null,
    };
  },
  async created() {
    await this.fetchUserData();
    await this.fetchColumns();
  },
  methods: {
    async moveCard({ card, fromColumn, toColumn }) {
      const fromIndex = this.columns.findIndex(column => column.column_id === fromColumn);
      const toIndex = this.columns.findIndex(column => column.column_id === toColumn);
      if (fromIndex !== -1 && toIndex !== -1) {
        const cardIndex = this.columns[fromIndex].cards.findIndex(c => c.card_id === card.card_id);
        if (cardIndex !== -1) {
          const [movedCard] = this.columns[fromIndex].cards.splice(cardIndex, 1);
          movedCard.column_id = toColumn;
          this.columns[toIndex].cards.push(movedCard);
          await this.updateCardsOrder(toColumn, this.columns[toIndex].cards); // Save changes on the server
        }
      }
    },
    async updateColumn(index, updatedColumn) {
      this.columns = this.columns.map((column, i) =>
        i === index ? { ...column, ...updatedColumn } : column
      );

      const color = JSON.stringify(updatedColumn.color);

      try {
        const response = await axios.post('http://localhost/X-men/back/update_column.php', {
          id: updatedColumn.column_id,
          column_name: updatedColumn.column_name,
          color: color
        });

        if (!response.data.success) {
          throw new Error('Ошибка базы данных: ' + response.data.error);
        }
        console.log('Колонка успешно обновлена.');
      } catch (error) {
        console.error('Ошибка при обновлении колонки:', error.message);
        alert('Не удалось обновить колонку. Попробуйте снова позже.');
      }
    },
    updateCardOrderInColumn(columnId) {
      const column = this.columns.find(col => col.column_id === columnId);
      if (column) {
        column.cards.forEach((card, index) => {
          card.order = index;
        });
      }
    },
    async updateCardOrder() {
  const updatedCards = [];
  this.columns.forEach((column) => {
    column.cards.forEach((card, index) => {
      updatedCards.push({ card_id: card.card_id, column_id: column.column_id, order: index });
    });
  });

  try {
    const response = await axios.post('http://localhost/X-men/back/update_card_order.php', updatedCards);
    if (response.data.success) {
      console.log('Порядок карточек успешно обновлен');
    } else {
      console.error('Ошибка при обновлении порядка карточек:', response.data.error);
    }
  } catch (error) {
    console.error('Network Error:', error);
  }
}
,
    async createCard() {
      const newCard = {
        id: Date.now(),
        title: '',
        content: '',
        color: '#666666',
      };
      try {
        this.localCards.push(newCard);
        this.$emit('updateCards', this.localCards);
      } catch (error) {
        console.error('Error creating card:', error);
      }
    },
    onCreateCard(columnId) {
      const cardData = {
        title: this.newCardTitle,
        description: this.newCardDescription
      };
      this.createCard(columnId, cardData);
    },
    async createColumn() {
      if (!this.loggedInUser) {
        console.error('Ошибка: не удалось получить идентификатор пользователя.');
        return;
      }

      const newColumn = {
        column_name: 'New Column',
        user_id: localStorage.getItem('userid'),
        color: JSON.stringify(['#d9d9d9', '#d9d9d9', '#d9d9d9']),
        order: this.columns.length
      };

      try {
        const response = await fetch('http://localhost/X-men/back/create_column.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(newColumn),
        });

        const data = await response.json();

        if (data.id) {
          newColumn.column_id = data.id;
          newColumn.cards = [];
          newColumn.color = ['#d9d9d9', '#d9d9d9', '#d9d9d9'];
          this.columns.push(newColumn);
        } else {
          console.error('Ошибка при создании колонки:', data.error || 'Неизвестная ошибка');
        }
      } catch (err) {
        console.error('Ошибка при создании колонки:', err);
      }
    },
    async deleteColumn(index) {
      const column = this.columns[index];
      try {
        const response = await fetch('http://localhost/X-men/back/delete_column.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id: column.column_id }),
        });
        const data = await response.json();
        if (data.success) {
          this.columns.splice(index, 1);
        }
      } catch (err) {
        console.error('Ошибка при удалении колонки:', err);
      }
    },
    toggleEditMode() {
      this.isEditMode = !this.isEditMode;
    },
    updateOrder() {
      const reorderedColumns = this.columns.map((column, index) => ({
        column_id: column.column_id,
        order: index
      }));

      axios.post('http://localhost/X-men/back/update_column_order.php', reorderedColumns)
        .then(response => {
          if (response.data.success) {
            console.log('Column order successfully updated');
          } else {
            console.error('Error updating column order:', response.data.error);
          }
        })
        .catch(error => {
          console.error('Network Error:', error);
        });
    },
    logout() {
      sessionStorage.removeItem('loggedInUser');
      localStorage.removeItem('userid');
      localStorage.removeItem('username');
      this.loggedInUser = null;
      this.$router.push('/login');
    },
    async fetchUserData() {
      try {
        const response = await fetch("http://localhost/X-men/back/login.php", {
          method: 'GET',
          credentials: 'include',
        });
        const data = await response.json();

        if (data.success && data.user_found) {
          this.loggedInUser = data.login;
          sessionStorage.setItem('loggedInUser', this.loggedInUser);
        } else {
          console.error('Ошибка при получении данных о пользователе:', data);
        }
      } catch (error) {
        console.error("Ошибка при получении данных о пользователе:", error);
      }
    },
    async fetchColumns() {
      try {
        const userId = localStorage.getItem('userid');
        const response = await fetch(`http://localhost/X-men/back/get_columns.php?user_id=${userId}`);
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        this.columns = data.map(column => {
          if (typeof column.color === 'string') {
            column.color = JSON.parse(column.color);
          }
          return column;
        });

        this.columns.sort((a, b) => a.order - b.order);
        this.columns.forEach(column => {
          column.cards.sort((a, b) => a.order - b.order);
        });
      } catch (err) {
        console.error('Ошибка при загрузке колонок:', err);
      }
    },
    async updateCardsOrder(columnId, cards) {
      try {
        const updatedCards = cards.map((card, index) => ({
          card_id: card.card_id,
          column_id: columnId,
          order: index
        }));

        console.log('Updating cards order:', updatedCards); // Debugging log

        const response = await axios.post('http://localhost/X-men/back/update_card_order.php', updatedCards);

        if (response.data.success) {
          console.log('Card order successfully updated');
        } else {
          console.error('Error updating card order:', response.data.error);
        }
      } catch (error) {
        console.error('Network Error:', error);
      }
    }
  },
  mounted() {
    const storedUserId = localStorage.getItem('username');
    if (storedUserId) {
      this.loggedInUser = storedUserId;
    }
  }
};
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

.board-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  font-family: 'Inter', sans-serif;
  /* height: 100vh;
  overflow: hidden; */
}

.board-header {
  display: flex;
  justify-content: space-between;
  width: 100%;
  padding: 20px 0;
}

.board-controls {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.create-column-btn,
.edit-columns-btn {
  padding: 10px 20px;
  margin-top: 25px;
  border-radius: 8px;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
  width: 300px;
}

.create-column-btn {
  background-color: #1362a0;
  color: white;
}

.edit-columns-btn {
  background-color: #d9d9d9;
  color: #032a4e;
}

.board-columns {
  display: flex;
  flex-direction: row;
  gap: 20px;
  width: 100%;
  overflow-x: auto;
}

.column-wrapper {
  position: relative;
}

.color-controls {
  display: flex;
  justify-content: center;
  gap: 5px;
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
}

.color-circle {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  cursor: pointer;
}

.color-picker-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.color-picker {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.color-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

.color-option {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  position: fixed;
  top: 35px;
  right: 4%;
}

.user-details {
  width: 241px;
  /* Возвращаем ширину блока к предыдущему значению */
  height: 45px;
  padding: 5px 10px;
  /* Уменьшаем отступы внутри блока */
  border-radius: 12px;
  /* Уменьшаем радиус закругления */
  overflow: hidden;
  border: 2px solid #295EA9;
  background: #fff;
  display: flex;
  justify-content: space-between;
  /* Изменяем выравнивание на центральное */
  align-items: center;
}

.user-name {
  text-align: center;
  /* Выравниваем текст по центру */
  color: #032A4E;
  font-size: 16px;
  font-family: Inter, sans-serif;
  font-weight: 700;
  word-wrap: break-word;
  flex: 1;
  /* Занимаем оставшееся пространство */
}

.logout-icon {
  width: 20px;
  height: 20px;
  cursor: pointer;
  /* Делаем курсор указателем при наведении */
  /* Изменяем позиционирование иконки */
  margin-right: 5px;
  /* Отодвигаем иконку от правого края */
}


.fixed-bar {
  position: fixed;
  top: 0;
  left: 4%;
  z-index: 1000;
  /* Устанавливаем z-index, чтобы контроли оставались поверх других элементов */
  padding: 10px;
  /* Добавляем отступы для улучшения визуального восприятия */
}
</style>