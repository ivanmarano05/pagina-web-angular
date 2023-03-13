import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { HashLocationStrategy, LocationStrategy  } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { LoginComponent } from './login/login.component';
import { NavMenuComponent } from './nav-menu/nav-menu.component';
import { HomeComponent } from './home/home.component';
import { InicioComponent } from './inicio/inicio.component';
import { CrearEquipoComponent } from './crear-equipo/crear-equipo.component';
import { MiEquipoComponent } from './mi-equipo/mi-equipo.component';
import { VerEquiposComponent } from './ver-equipos/ver-equipos.component';
import { DetallesEquipoComponent } from './detalles-equipo/detalles-equipo.component';
import { AdministradorComponent } from './administrador/administrador.component';
import { ForoComponent } from './foro/foro.component';

import { AuthGuard } from './guard/auth-guard.service';
import { AutInterceptor } from './aut-interceptor';

@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    NavMenuComponent,
    HomeComponent,
    InicioComponent,
    CrearEquipoComponent,
    MiEquipoComponent,
    VerEquiposComponent,
    DetallesEquipoComponent,
    AdministradorComponent,
    ForoComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    HttpClientModule,
    ReactiveFormsModule
  ],
  providers: [
    AuthGuard,
    { provide: HTTP_INTERCEPTORS, useClass: AutInterceptor, multi: true },
    {provide : LocationStrategy , useClass: HashLocationStrategy},
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
