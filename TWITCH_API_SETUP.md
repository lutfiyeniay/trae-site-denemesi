# 🎮 Twitch API Kurulum Rehberi

## 📋 Gerekli Adımlar

### 1. Twitch Developer Console'a Giriş
1. [Twitch Developer Console](https://dev.twitch.tv/console) adresine gidin
2. Twitch hesabınızla giriş yapın
3. "Applications" sekmesine tıklayın

### 2. Yeni Uygulama Oluşturma
1. "Register Your Application" butonuna tıklayın
2. Uygulama bilgilerini doldurun:
   - **Name**: `LutfiCyber Stream Site`
   - **OAuth Redirect URLs**: `http://localhost:8080`
   - **Category**: `Website Integration`
3. "Create" butonuna tıklayın

### 3. Client ID ve Secret Alma
1. Oluşturduğunuz uygulamaya tıklayın
2. **Client ID**'yi kopyalayın
3. "New Secret" butonuna tıklayarak **Client Secret** oluşturun

### 4. OAuth Token Alma
OAuth token almak için aşağıdaki URL'yi tarayıcınızda açın:

```
https://id.twitch.tv/oauth2/authorize?client_id=YOUR_CLIENT_ID&redirect_uri=http://localhost:8080&response_type=token&scope=chat:read+chat:edit+channel:read:subscriptions
```

**YOUR_CLIENT_ID** yerine gerçek Client ID'nizi yazın.

### 5. API Anahtarlarını Güncelleme

`js/script.js` dosyasında aşağıdaki değerleri güncelleyin:

```javascript
// TwitchChatAPI sınıfında
this.clientId = 'YOUR_TWITCH_CLIENT_ID'; // Buraya Client ID'nizi yazın
this.accessToken = 'YOUR_TWITCH_ACCESS_TOKEN'; // Buraya OAuth token'ınızı yazın

// TwitchAPI sınıfında da aynı değerleri güncelleyin
this.clientId = 'YOUR_TWITCH_CLIENT_ID';
this.accessToken = 'YOUR_TWITCH_ACCESS_TOKEN';
```

## 🔧 API Özellikleri

### Chat API Endpoints
- **Chat Settings**: `https://api.twitch.tv/helix/chat/settings`
- **Send Message**: `https://api.twitch.tv/helix/chat/messages`
- **User Info**: `https://api.twitch.tv/helix/users`

### Mevcut Fonksiyonlar
- ✅ Chat ayarlarını alma
- ✅ Kullanıcı ID'si alma
- ✅ Mesaj gönderme (Helix API)
- ✅ WebSocket fallback
- ✅ Otomatik yeniden bağlanma
- ✅ Hata yönetimi

## 🚀 Test Etme

1. API anahtarlarını güncelledikten sonra
2. Web sunucusunu başlatın
3. Chat bağlantı butonuna tıklayın
4. Console'da API yanıtlarını kontrol edin

## ⚠️ Önemli Notlar

- OAuth token'ın `chat:read`, `chat:edit` ve `channel:read:subscriptions` izinlerine sahip olması gerekir
- Token'lar belirli bir süre sonra sona erer, yenilenmesi gerekebilir
- API rate limit'leri vardır, çok sık istek göndermeyin
- Güvenlik için API anahtarlarını asla public repository'lerde paylaşmayın

## 🔍 Hata Ayıklama

### 401 Unauthorized
- OAuth token eksik veya geçersiz
- Client ID yanlış
- Token'ın gerekli izinleri yok

### 403 Forbidden
- Token'ın bu işlem için yetkisi yok
- Channel'a erişim izni yok

### 429 Too Many Requests
- Rate limit aşıldı
- İstekleri azaltın

## 📞 Destek

Herhangi bir sorun yaşarsanız:
1. Browser console'unu kontrol edin
2. Network sekmesinde API isteklerini inceleyin
3. Twitch Developer Documentation'ı okuyun