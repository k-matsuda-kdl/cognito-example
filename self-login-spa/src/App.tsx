import './App.css'
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { RequireAuth } from './RequireAuth';
import { MyPage } from './pages/MyPage';
import { ChangePassword } from './pages/ChangePassword';
import { SetupTOTP } from './pages/SetupTOTP';
import { SetupSMS } from './pages/SetupSMS';
import { MfaSettings } from './pages/MfaSettings';
import { Login } from './pages/Login';
import { SetupPasskey } from './pages/SetupPasskey';

function App() {

  return (
    <>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route
            path="/"
            element={
              <RequireAuth>
                <MyPage />
              </RequireAuth>
            }
          />
          <Route
            path="/changepassword"
            element={
              <RequireAuth>
                <ChangePassword />
              </RequireAuth>
            }
          />
          <Route
            path="/setuptotp"
            element={
              <RequireAuth>
                <SetupTOTP />
              </RequireAuth>
            }
          />
          <Route
            path="/setupsms"
            element={
              <RequireAuth>
                <SetupSMS />
              </RequireAuth>
            }
          />
          <Route
            path="/mfasettings"
            element={
              <RequireAuth>
                <MfaSettings />
              </RequireAuth>
            }
          />
          <Route
            path="/setuppasskey"
            element={
              <RequireAuth>
                <SetupPasskey />
              </RequireAuth>
            }
          />
        </Routes>
      </BrowserRouter>
    </>
  )
}

export default App
