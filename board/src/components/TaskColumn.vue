<template>
  <div class="column">
    <div class="column-header">
      <div class="color-circles">
        <div
          v-for="(color, index) in lastSelectedColors"
          :key="index"
          :style="{ backgroundColor: color }"
          class="color-circle"
          @click="openColorPicker"
        ></div>
      </div>
      <button class="delete-column-btn" @click="confirmDeleteColumn">
        <i class="fas fa-trash"></i>
      </button>
    </div>
    <div :style="{ backgroundColor: backgroundColor }" class="column-body">
      <div class="column-title" :style="{ color: textColor }">
        <div @dblclick="toggleEditMode">
          <span v-if="!isEditMode && !localTitle">{{ 'New Column' }}</span>
          <span v-if="!isEditMode && localTitle">{{ localTitle }}</span>
          <input
            v-else
            v-model="localTitle"
            @blur="saveColumn"
            @keydown.enter="saveColumn"
            class="column-input"
          />
        </div>
      </div>
      <button class="create-card-btn" @click="createCard">
        <span class="btn-text">Создать карточку</span>
      </button>
      <draggable v-model="localCards" group="cards" itemKey="id" class="cards" @end="updateCardOrder">
        <template #item="{ element, index }">
          <TaskCard
            :card="element"
            @updateCard="updateCard(index, $event)"
            @deleteCard="deleteCard(index)"
            @changeColor="changeCardColor(index, $event)"
          />
        </template>
      </draggable>
      <div v-if="showColorPicker" class="color-picker-overlay" @click="closeColorPicker">
        <div class="color-picker" @click.stop>
          <h3>Выберите цвет</h3>
          <div class="color-options">
            <div
              v-for="color in availableColors"
              :key="color"
              class="color-option"
              :style="{ backgroundColor: color }"
              @click="selectColor(color)"
            ></div>
          </div>
        </div>
      </div>
      <div v-if="showDeleteConfirmation" class="delete-confirmation">
        <p>Вы точно хотите удалить колонку и ее содержимое?</p>
        <div class="confirmation-buttons">
          <button class="confirm-btn" @click="deleteColumn">Да</button>
          <button class="cancel-btn" @click="hideDeleteConfirmation">Нет</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TaskCard from './TaskCard.vue';
import draggable from 'vuedraggable';

export default {
  name: 'TaskColumn',
  components: {
    TaskCard,
    draggable,
  },
  props: {
    column: {
      type: Object,
      required: true,
      default: () => ({
        column_id: null,
        column_name: 'Новая колонка',
        cards: [],
        color: ['#d9d9d9', '#d9d9d9', '#d9d9d9'],
      }),
    },
    index: Number,
  },
  data() {
    return {
      isEditMode: false,
      localTitle: this.column.column_name || 'Новая колонка',
      localCards: this.column.cards || [],
      lastSelectedColors: (this.column.color && this.column.color.slice(-3)) || ['#d9d9d9', '#d9d9d9', '#d9d9d9'],
      showColorPicker: false,
      showDeleteConfirmation: false,
      availableColors: [
        '#666666', '#66664D', '#9900B3', '#991AFF', '#FF4D4D', '#B33300', '#999966', '#CC9999',
        '#CCCC00', '#999933', '#809980', '#4D8066', '#4DB380', '#B33300', '#E64D66', '#E6331A',
        '#FF5733', '#B34D4D', '#E6B3B3', '#FF1A66', '#E666FF', '#FF3380', '#FF33FF', '#1AB399',
        '#4DB3FF', '#66991A', '#99E6E6', '#80B300', '#33FFFF', '#00E680', '#33FFCC', '#66E64D',
        '#FFFF33', '#99FF99', '#E6FF80', '#FFB399', '#FFB3E6', '#FFD1DC', '#FFE6CC', '#FFFFCC'
      ],
    };
  },
  computed: {
    backgroundColor() {
      return this.lastSelectedColors[0]; // Использует первый цвет из последних выбранных
    },
    textColor() {
      return this.getContrastingTextColor(this.lastSelectedColors[0]);
    },
  },
  methods: {
    toggleEditMode() {
      this.isEditMode = !this.isEditMode;
    },
    checkMove(event) {
      return event.from !== event.to;
    },
    onCardDrop(event) {
      if (event.from !== event.to) {
        this.$emit('moveCard', { card: event.item, fromColumn: event.from.dataset.columnId, toColumn: event.to.dataset.columnId });
      }
    },
    async onDragEnd() {
    const updatedCards = this.localCards.map((card, index) => ({
      ...card,
      order: index,
      column_id: this.column.column_id,
    }));

    try {
      await this.$parent.updateCardsOrder(this.column.column_id, updatedCards);
    } catch (error) {
      console.error('Ошибка при обновлении порядка карточек:', error);
    }
  },
    async saveColumn() {
      try {
        this.isEditMode = false;
        const updatedColumn = {
          column_id: this.column.column_id,
          column_name: this.localTitle,
          color: this.lastSelectedColors,
        };
        // Emit the updated column data to the parent component
        this.$emit('updateColumn', updatedColumn);
        // Immediately save the column name change to the backend
        const response = await fetch('http://localhost/X-men/back/update_column.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(updatedColumn),
        });
        const data = await response.json();
        if (data.success) {
          console.log('Название колонки успешно обновлено');
        } else {
          console.error('Ошибка при обновлении названия колонки:', data.error);
        }
      } catch (error) {
        console.error('Ошибка при сохранении колонки:', error);
      }
    },
    async createCard() {
      const newCard = {
        card_id: Date.now(),
        name: '',
        text: '',
        color: '#666666', // Default color for new cards
        column_id: this.column.column_id, // добавляем column_id для привязки карточки к колонке
        order: this.localCards.length // порядок карточки в колонке
      };
      try {
        // Отправляем новый объект карточки на сервер для добавления в БД
        const response = await fetch('http://localhost/X-men/back/create_card.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(newCard),
        });
        const data = await response.json();
        if (data.id) {
          // Обновляем локальный id новосозданной карточки
          newCard.card_id = data.id;
          // Add new card locally
          this.localCards.push(newCard);
          // Emit an event to update cards in the parent component
          this.$emit('updateCards', this.localCards);
        } else {
          console.error('Ошибка при создании карточки:', data.error || 'Неизвестная ошибка');
        }
      } catch (error) {
        console.error('Error creating card:', error);
      }
    },
    async updateCard(cardIndex, newCard) {
        try {
          const response = await fetch('http://localhost/X-men/back/update_card.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(newCard),
          });
          const data = await response.json();
          if (data.success) {
            this.localCards.splice(cardIndex, 1, newCard);
            this.$emit('updateCards', this.localCards);
          } else {
            console.error('Failed to update card:', data.error || 'Unknown error');
          }
        } catch (err) {
          console.error('Error updating card:', err);
        }
      },
      async deleteCard(cardIndex) {
        const cardId = this.localCards[cardIndex].card_id;
        try {
          const response = await fetch('http://localhost/X-men/back/delete_card.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: cardId }),
          });
          const data = await response.json();
          if (data.success) {
            this.localCards.splice(cardIndex, 1);
            this.$emit('updateCards', this.localCards);
          }
        } catch (err) {
          console.error('Ошибка:', err);
        }
      },
    async updateCardOrder() {
      const updatedCards = this.localCards.map((card, index) => ({
        card_id: card.card_id,
        column_id: this.column.column_id,
        order: index
      }));

      try {
        const response = await fetch('http://localhost/X-men/back/update_card_order.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(updatedCards),
        });
        const data = await response.json();
        if (data.success) {
          console.log('Card order successfully updated');
          this.$emit('updateCards', this.localCards);
        } else {
          console.error('Error updating card order:', data.error);
        }
      } catch (error) {
        console.error('Network Error:', error);
      }
    },
    async changeCardColor(cardIndex, color) {
      const card = this.localCards[cardIndex];
      card.color = color;
      try {
        const response = await fetch('http://localhost/X-men/back/update_card.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(card),
        });
        const data = await response.json();
        if (data.success) {
          this.$emit('updateCards', this.localCards);
        } else {
          console.error('Ошибка при изменении цвета карточки:', data.error || 'Неизвестная ошибка');
        }
      } catch (err) {
        console.error('Ошибка при изменении цвета карточки:', err);
      }
    },
    openColorPicker() {
      this.showColorPicker = true;
    },
    closeColorPicker() {
      this.showColorPicker = false;
    },
    selectColor(color) {
      this.lastSelectedColors = [color, ...this.lastSelectedColors.slice(0, 2)];
      this.showColorPicker = false;
      this.$emit('changeColumnColor', this.index, color);
      this.saveColumn(); 
    },
    confirmDeleteColumn() {
      this.showDeleteConfirmation = true;
    },
    hideDeleteConfirmation() {
      this.showDeleteConfirmation = false;
    },
    async deleteColumn() {
      try {
        const response = await fetch('http://localhost/X-men/back/delete_column.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id: this.column.column_id }),
        });
        const data = await response.json();
        if (data.success) {
          this.$emit('deleteColumn', this.index);
        }
      } catch (err) {
        console.error('Ошибка при удалении колонки:', err);
      } finally {
        this.hideDeleteConfirmation();
      }
    },
    getContrastingTextColor(backgroundColor) {
        const color = backgroundColor.charAt(0) === '#' ? backgroundColor.substring(1, 7) : backgroundColor;
        const r = parseInt(color.substring(0, 2), 16);
        const g = parseInt(color.substring(2, 4), 16);
        const b = parseInt(color.substring(4, 6), 16);
        const uicolors = [r / 255, g / 255, b / 255];
        const c = uicolors.map((col) => {
          if (col <= 0.03928) {
            return col / 12.92;
          }
          return Math.pow((col + 0.055) / 1.055, 2.4);
        });
        const L = 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
        return L > 0.179 ? '#000000' : '#FFFFFF';
      },
    },
};
</script>



<style scoped>
.column {
  display: flex;
  flex-direction: column;
  height: 100%; /* Устанавливаем высоту .column на 100% */

  width: 300px;
  background-color: #eee;
  padding: 0;
  border-radius: 8px;
  font-family: 'Inter', sans-serif;
}

.column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
}

.color-circles {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.color-circle {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
}

.delete-column-btn {
  width: 20px;
  height: 20px;
  background-color: red;
  border: none;
  padding: 0;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0;
}

.delete-column-btn:hover {
  background-color: darkred;
}

.column-body {
  flex: 1; 
  background-color: inherit;
  border-radius: 8px;
  padding: 10px;
}

.column-title {
  font-size: 1.2em;
  font-weight: 600;
  cursor: pointer;
  word-wrap: break-word;
  white-space: normal;
}

.column-input {
  font-size: 1.2em;
  font-weight: 600;
  width: 100%;
  border: none;
  outline: none;
}

.create-card-btn {
  position: relative;
  background-color: #d9d9d9;
  border: none;
  padding: 10px 10px;
  border-radius: 5px;
  cursor: pointer;
  transition: height 0.2s ease;
  width: 100%;
  margin-top: 10px;
  height: 50px;
}

.create-card-btn .btn-text {
  color: #666666;
  transition: color 0.3s ease;
}

.create-card-btn:hover {
  background-color: #f4ae5b;
  height: 80px;
  transition: background-color 0.2s ease;
}

.create-card-btn::before {
  content: '+'; 
  font-size: 1.5em; 
  color: #666666; /* Серый цвет плюса */
  position: absolute; 
  left: 15px; 
  top: 50%; 
  transform: translateY(-55%); 
  transition: font-size 0.2s ease, color 0.2s ease; /* Добавляем переходы для изменения размера и цвета плюса */
}

.create-card-btn:hover::before {
  font-size: 3em;
  left: 40px;  
  color: black; /* Изменяем цвет плюса на белый при наведении */
}

.create-card-btn:hover .btn-text {
  color: black;
  transform: translateX(20px); 
  white-space: normal; 
  display: inline-block; 
  padding-left: 30px; 
  padding-right: 40px; 
}

.btn-text {
  margin-left: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-text::before {
  margin-right: 10px;
  font-size: 1.5em;
}

.cards {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 10px;
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
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 10px;
  margin-top: 10px;
}

.color-option {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
}

.delete-confirmation {
  position: fixed;
  top: 80%; /* Размещаем по вертикали на середине экрана */
  left: 50%; /* Размещаем по горизонтали на середине экрана */
  transform: translate(-50%, -20%); /* Центрируем по центру экрана */
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
  z-index: 999;
}

.confirmation-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
}

.confirm-btn {
  background-color: green;
  color: white;
  border: none;
  padding: 10px;
  margin: 5px;
  border-radius: 5px;
  cursor: pointer;
}

.cancel-btn {
  background-color: red;
  color: white;
  border: none;
  padding: 10px;
  margin: 5px;
  border-radius: 5px;
  cursor: pointer;
}
</style>