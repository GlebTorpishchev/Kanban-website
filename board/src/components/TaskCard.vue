<template>
  <div class="card" :style="{ backgroundColor: card.color, color: textColor }">
    <div v-if="!isEditMode" class="card-header">
      <span @dblclick="toggleEditMode" class="card-title">{{ displayTitle }}</span>
      <div @click="openColorPicker" class="color-box" :style="{ borderColor: borderColor }"></div>
    </div>
    <div v-if="isEditMode" class="card-header">
      <input v-model="localTitle" @blur="saveCard" @keydown.enter="saveCard" placeholder="New card" class="card-input" />
    </div>
    <div v-if="!isEditMode" class="card-description" @dblclick="toggleEditMode">
      <span v-html="formattedDescription"></span>
    </div>
    <div v-if="isEditMode" class="card-description">
      <textarea v-model="localDescription" @blur="saveCard" @keydown.ctrl.enter="newLine" rows="3" placeholder="Введите описание"></textarea>
      <button class="save-card-btn" @click="saveCard">Сохранить</button>
    </div>
    <button v-if="!isEditMode" class="delete-card-btn" @click="deleteCard">
      <i class="fas fa-trash"></i>
      <span>удалить</span>
    </button>
    <div v-if="showColorPicker" class="color-picker-overlay" @click="closeColorPicker">
      <div class="color-picker" @click.stop>
        <h3 class="color-picker-label">Выберите цвет</h3>
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
  </div>
</template>

<script>
export default {
  name: 'TaskCard',
  props: {
    card: Object,
  },
  data() {
    return {
      isEditMode: false,
      localTitle: this.card.name,
      localDescription: this.card.text,
      showColorPicker: false,
      availableColors: [
        '#666666', '#66664D', '#9900B3', '#991AFF', '#FF4D4D', '#B33300', '#999966', '#CC9999',
        '#CCCC00', '#999933', '#809980', '#4D8066', '#4DB380', '#B33300', '#E64D66', '#E6331A',
        '#FF5733', '#B34D4D', '#E6B3B3', '#FF1A66', '#E666FF', '#FF3380', '#FF33FF', '#1AB399',
        '#4DB3FF', '#66991A', '#99E6E6', '#80B300', '#33FFFF', '#00E680', '#33FFCC', '#66E64D',
        '#FFFF33', '#99FF99', '#E6FF80', '#FFB399', '#FFB3E6', '#FFD1DC', '#FFE6CC', '#FFFFCC'
      ],
    };
  },
  methods: {
    toggleEditMode() {
      this.isEditMode = !this.isEditMode;
      if (this.isEditMode && this.localTitle === 'Новая карточка') {
        this.localTitle = '';
      }
    },
    async saveCard() {
      const cardData = { ...this.card, name: this.localTitle, text: this.localDescription };
      try {
        const response = await fetch('http://localhost/X-men/back/update_card.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(cardData),
        });
        const data = await response.json();
        if (data.success) {
          this.$emit('updateCard', cardData);
        }
        this.isEditMode = false;
      } catch (err) {
        console.error('Ошибка:', err);
      }
    },
    deleteCard() {
      this.$emit('deleteCard');
    },
    openColorPicker() {
      this.showColorPicker = true;
    },
    closeColorPicker() {
      this.showColorPicker = false;
    },
    async selectColor(color) {
      const cardData = { ...this.card, color: color };
      try {
        const response = await fetch('http://localhost/X-men/back/update_card.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(cardData),
        });
        const data = await response.json();
        if (data.success) {
          this.$emit('updateCard', cardData);
        } else {
          console.error('Ошибка при изменении цвета карточки:', data.error || 'Неизвестная ошибка');
        }
      } catch (err) {
        console.error('Ошибка при изменении цвета карточки:', err);
      }
      this.closeColorPicker();
    },
    newLine() {
      this.localDescription += '\n';
    },
  },
  computed: {
    textColor() {
      const color = this.card.color.replace('#', '');
      const r = parseInt(color.substring(0, 2), 16);
      const g = parseInt(color.substring(2, 4), 16);
      const b = parseInt(color.substring(4, 6), 16);
      const brightness = (r * 299 + g * 587 + b * 114) / 1000;
      return brightness > 125 ? 'black' : 'white';
    },
    borderColor() {
      const color = this.card.color.replace('#', '');
      const r = parseInt(color.substring(0, 2), 16);
      const g = parseInt(color.substring(2, 4), 16);
      const b = parseInt(color.substring(4, 6), 16);
      const brightness = (r * 299 + g * 587 + b * 114) / 1000;
      return brightness > 125 ? 'black' : 'white';
    },
    formattedDescription() {
      return this.localDescription ? this.localDescription.replace(/\n/g, '<br/>') : 'Описание<br/><i>Введите двойным кликом</i>';
    },
    displayTitle() {
      return this.localTitle || 'New card';
    },
  },
  watch: {
    card: {
      handler(newVal) {
        this.localTitle = newVal.name;
        this.localDescription = newVal.text;
      },
      deep: true,
    },
  },
};
</script>


<style scoped>
.card {
  background-color: #f4ae5b;
  padding: 10px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 5px;
  color: black;
  font-family: 'Inter', sans-serif;
  word-wrap: break-word;
}

.card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.color-box {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  background-color: inherit;
  border: 2px solid;
  border-radius: 4px;
  cursor: pointer;
}

.card-title {
  font-weight: 600;
  flex-grow: 1;
  margin-right: 10px;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.card-input {
  width: 100%;
  border: none;
  outline: none;
  font-weight: 600;
  margin-bottom: 5px;
  overflow: hidden;
}

.card-description textarea {
  width: 100%;
  height: auto;
  resize: none;
  font-family: 'Inter', sans-serif;
  font-weight: 400;
}

.card-description span {
  white-space: pre-wrap;
  word-wrap: break-word;
}

.save-card-btn {
  background-color: white;
  color: green;
  padding: 10px 20px;
  text-align: center;
  cursor: pointer;
  margin-top: 10px;
}

.delete-card-btn {
  background-color: #d9d9d9;
  width: 100%;
  height: 0px;
  opacity: 0.3;
  transition: opacity 0.2s ease;
}

.delete-card-btn:hover {
  opacity: 1;
}

.card:hover .delete-card-btn {
  height: 40px;
  transition: height 0.2s ease;
}

.delete-card-btn span {
  display: none;
}

.delete-card-btn:hover span {
  display: inline;
  color: red;
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
  border-radius: 4px;
  cursor: pointer;
}

.color-picker-label {
  color: black !important;
  font-weight: bold !important;
}
</style>
