import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { InicioComponent } from './inicio/inicio.component';
import { CrearEquipoComponent } from './crear-equipo/crear-equipo.component';
import { MiEquipoComponent } from './mi-equipo/mi-equipo.component';
import { VerEquiposComponent } from './ver-equipos/ver-equipos.component';
import { DetallesEquipoComponent } from './detalles-equipo/detalles-equipo.component';
import { AdministradorComponent } from './administrador/administrador.component';
import { ForoComponent } from './foro/foro.component';
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';

import { AuthGuard } from './guard/auth-guard.service';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'inicio', component: InicioComponent, canActivate: [AuthGuard] },
  { path: 'crearEquipo', component: CrearEquipoComponent, canActivate: [AuthGuard] },
  { path: 'miEquipo', component: MiEquipoComponent, canActivate: [AuthGuard] },
  { path: 'verEquipos', component: VerEquiposComponent, canActivate: [AuthGuard] },
  { path: 'detallesEquipo/:id', component: DetallesEquipoComponent, canActivate: [AuthGuard] },
  { path: 'administrador', component: AdministradorComponent, canActivate: [AuthGuard] },
  { path: 'foro', component: ForoComponent, canActivate: [AuthGuard] }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
